<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\SentMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use function Safe\file_get_contents;
use function Safe\json_decode;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->maxLength(255),
                Forms\Components\TextInput::make('website')
                    ->maxLength(255),
                Forms\Components\Select::make('customer_group_id')
                    ->label('Gruppo Cliente')
                    ->relationship('customerGroup', 'name')
                    ->searchable()
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'not_contacted'  => 'Non contattato',
                        'in_contact'     => 'In contatto',
                        'in_negotiation' => 'In trattativa',
                        'converted'      => 'Convertito',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('import_csv')
                    ->label('Importa CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('csv_file')
                            ->label('Seleziona il file CSV')
                            ->disk('local')
                            ->acceptedFileTypes(['text/csv', 'text/plain', '.csv'])
                            ->required()
                            ->maxFiles(1),
                    ])
                    ->action(function (array $data) {
                        $file = $data['csv_file'];

                        if (is_string($file)) {
                            if (! Storage::disk('local')->exists($file)) {
                                throw new \Exception('File non trovato: '.$file);
                            }
                            $content = Storage::disk('local')->get($file);
                        } else {
                            $content = file_get_contents($file->getRealPath());
                        }

                        $rows = array_map('str_getcsv', explode(PHP_EOL, $content));
                        $header = array_shift($rows);

                        foreach ($rows as $row) {
                            if (count($row) < 4 || empty($row[0])) {
                                continue;
                            }
                            Customer::create([
                                'name'    => $row[0],
                                'email'   => $row[1],
                                'phone'   => $row[2],
                                'website' => $row[3],
                            ]);
                        }
                    })
                    ->successNotificationTitle('Import CSV completato!'),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customerGroup.name')
                ->label('Gruppo Cliente')
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'not_contacted'  => 'Non contattato',
                        'in_contact'     => 'In contatto',
                        'in_negotiation' => 'In trattativa',
                        'converted'      => 'Convertito',
                    ]),
                Tables\Filters\SelectFilter::make('customer_group_id')
                    ->label('Gruppo Cliente')
                    ->relationship('customerGroup', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Azione singola per generare riassunto ed email
                Tables\Actions\Action::make('scrape_website')
                    ->label('Genera Riassunto ed Email')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\Textarea::make('summary')
                            ->label('Riassunto generato')
                            ->disabled(),
                        Forms\Components\Textarea::make('email_prompt')
                            ->label('Prompt per Email')
                            ->default('Descrivi il prodotto in maniera amichevole, come se stessi scrivendo ad un amico.')
                            ->lazy()
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                $summary = $get('summary');
                                if (! $summary) {
                                    return;
                                }
                                $finalPrompt = "Genera un'email senza oggetto seguendo questo schema:\n\n"
                                    ."Buongiorno,\n\n"
                                    ."sono Davide Cavallini.\n\n"
                                    ."Ho visitato il vostro sito e mi ha colpito [PRENDI TRE PUNTI DAL SUMMARY CHE E' QUESTO: '".$summary."'].\n\n"
                                    ."Credo che potreste avvantaggiarvi di\n\n"
                                    ."[DESCRIVI IN TRE RIGHE QUESTO PRODOTTO '".$state."']\n\n"
                                    ."per motivi che stanno nel summary.\n\n"
                                    ."Ecco il link per la demo: LINK DIRETTO SENZA PARENTESI.\n\n"
                                    ."Grazie e cordiali saluti,\n"
                                    .'Davide';

                                $openaiApiKey = env('OPENAI_API_KEY');
                                if (! $openaiApiKey) {
                                    $set('email_content', 'Chiave API OpenAI non configurata.');

                                    return;
                                }

                                $payload = [
                                    'model' => 'gpt-4o',
                                    'messages' => [
                                        ['role' => 'system', 'content' => 'Sei un assistente che genera email basate su un prompt fornito.'],
                                        ['role' => 'user', 'content' => $finalPrompt],
                                    ],
                                    'max_tokens' => 1024,
                                ];

                                $openaiEndpoint = 'https://api.openai.com/v1/chat/completions';

                                try {
                                    $httpClient = new Client();
                                    $apiResponse = $httpClient->post($openaiEndpoint, [
                                        'headers' => [
                                            'Authorization' => 'Bearer '.$openaiApiKey,
                                            'Content-Type'  => 'application/json',
                                        ],
                                        'json' => $payload,
                                    ]);
                                    $apiResult = json_decode($apiResponse->getBody()->getContents(), true);
                                    $emailContent = $apiResult['choices'][0]['message']['content'] ?? 'Nessun risultato';
                                    $emailContent = nl2br($emailContent);
                                } catch (\Exception $e) {
                                    $emailContent = 'Errore nella generazione: '.$e->getMessage();
                                }

                                $set('email_content', $emailContent);
                            }),
                        Forms\Components\RichEditor::make('email_content')
                            ->label('Email generata'),
                    ])
                    ->mountUsing(function (Customer $record, $livewire, $form) {
                        $website = $record->website;
                        if (! $website) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Il cliente non ha un sito web impostato.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $client = new Client([
                            'headers' => [
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '.
                                    'AppleWebKit/537.36 (KHTML, like Gecko) '.
                                    'Chrome/115.0.0.0 Safari/537.36',
                            ],
                            'timeout' => 10,
                        ]);

                        try {
                            $response = $client->get($website);
                            $html = $response->getBody()->getContents();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Errore durante lo scraping: '.$e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        //$textContent = strip_tags($html);

                        $dom = new \DOMDocument();
                        @$dom->loadHTML($html);
                        $body = $dom->getElementsByTagName('body')->item(0);
                        $textContent = $body ? strip_tags($dom->saveHTML($body)) : '';
                        $prompt = "Leggi il seguente testo estratto dal sito web del cliente e riassumi in poche frasi cosa fa il cliente:\n\n".$textContent;
                        $openaiApiKey = env('OPENAI_API_KEY');
                        if (! $openaiApiKey) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Chiave API OpenAI non configurata.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $payload = [
                            'model' => 'gpt-3.5-turbo',
                            'messages' => [
                                ['role' => 'system', 'content' => 'Sei un assistente che riassume il contenuto dei siti web.'],
                                ['role' => 'user', 'content' => $prompt],
                            ],
                            'max_tokens' => 150,
                        ];

                        $openaiEndpoint = 'https://api.openai.com/v1/chat/completions';

                        try {
                            $httpClient = new Client();
                            $apiResponse = $httpClient->post($openaiEndpoint, [
                                'headers' => [
                                    'Authorization' => 'Bearer '.$openaiApiKey,
                                    'Content-Type'  => 'application/json',
                                ],
                                'json' => $payload,
                            ]);
                            $apiResult = json_decode($apiResponse->getBody()->getContents(), true);
                            $summary = $apiResult['choices'][0]['message']['content'] ?? 'Nessun risultato';
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Errore nella chiamata a OpenAI: '.$e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        $form->fill([
                            'summary' => $summary,
                        ]);

                        Notification::make()
                            ->title('Successo')
                            ->body('Riassunto generato.')
                            ->success()
                            ->send();
                    })
                    ->action(function (array $data, Customer $record) {
                        $emailContent = $data['email_content'] ?? null;
                        if (! $emailContent) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Contenuto email non disponibile.')
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            Mail::html($emailContent, function ($message) use ($record) {
                                $message->to($record->email)
                                    ->subject('C’è una cosa interessante nel tuo sito internet.');
                            });

                            $record->update(['status' => 'in_contact']);

                            SentMessage::create([
                                'message' => $emailContent,
                                'contact' => $record->email,
                                'type'    => 'email',
                            ]);

                            Notification::make()
                                ->title('Successo')
                                ->body('Email inviata correttamente a '.$record->email)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Errore nell\'invio dell\'email: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalHeading('Genera Riassunto ed Email')
                    ->modalSubheading('Attendere mentre viene generato il riassunto e l\'email.'),
            ])
            ->bulkActions([
                // Bulk action per invio email a più record con notifica per ogni email inviata
                Tables\Actions\BulkAction::make('send_bulk_email')
                    ->label('Invia Email Bulk')
                    ->form([
                        Forms\Components\Textarea::make('email_prompt')
                            ->label('Prompt per Email')
                            ->default('Descrivi il prodotto in maniera amichevole, come se stessi scrivendo ad un amico.')
                            ->required(),
                    ])
                    ->action(function (array $data, \Illuminate\Support\Collection $records) {
                        $openaiApiKey = env('OPENAI_API_KEY');
                        if (! $openaiApiKey) {
                            Notification::make()
                                ->title('Errore')
                                ->body('Chiave API OpenAI non configurata.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $client = new Client([
                            'headers' => [
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '.
                                    'AppleWebKit/537.36 (KHTML, like Gecko) '.
                                    'Chrome/115.0.0.0 Safari/537.36',
                            ],
                            'timeout' => 10,
                        ]);

                        foreach ($records as $record) {
                            // Verifica che il record abbia un sito web
                            if (! $record->website) {
                                continue;
                            }

                            try {
                                $response = $client->get($record->website);
                                $html = $response->getBody()->getContents();
                            } catch (\Exception $e) {
                                continue;
                            }

                            //$textContent = strip_tags($html);

                            $dom = new \DOMDocument();
                            @$dom->loadHTML($html);
                            $body = $dom->getElementsByTagName('body')->item(0);
                            $textContent = $body ? strip_tags($dom->saveHTML($body)) : '';

                            $promptSummary = "Leggi il seguente testo estratto dal sito web del cliente e riassumi in poche frasi cosa fa il cliente:\n\n".$textContent;

                            // Genera il riassunto usando GPT-3.5-turbo
                            try {
                                $payloadSummary = [
                                    'model' => 'gpt-3.5-turbo',
                                    'messages' => [
                                        ['role' => 'system', 'content' => 'Sei un assistente che riassume il contenuto dei siti web.'],
                                        ['role' => 'user', 'content' => $promptSummary],
                                    ],
                                    'max_tokens' => 150,
                                ];
                                $openaiEndpoint = 'https://api.openai.com/v1/chat/completions';
                                $apiResponse = $client->post($openaiEndpoint, [
                                    'headers' => [
                                        'Authorization' => 'Bearer '.$openaiApiKey,
                                        'Content-Type'  => 'application/json',
                                    ],
                                    'json' => $payloadSummary,
                                ]);
                                $apiResult = json_decode($apiResponse->getBody()->getContents(), true);
                                $summary = $apiResult['choices'][0]['message']['content'] ?? 'Nessun risultato';
                            } catch (\Exception $e) {
                                continue;
                            }

                            // Costruisci il prompt finale per l'email
                            $finalPrompt = "Genera un'email senza oggetto seguendo questo schema:\n\n"
                                ."Buongiorno,\n\n"
                                ."sono Davide Cavallini.\n\n"
                                ."Ho visitato il vostro sito e mi ha colpito [PRENDI TRE PUNTI DAL SUMMARY CHE E' QUESTO: '".$summary."'].\n\n"
                                ."Credo che potreste avvantaggiarvi di\n\n"
                                ."[DESCRIVI IN TRE RIGHE QUESTO PRODOTTO '".$data['email_prompt']."']\n\n"
                                ."per motivi che stanno nel summary.\n\n"
                                ."Ecco il link per la demo: LINK DIRETTO SENZA PARENTESI.\n\n"
                                ."Grazie e cordiali saluti,\n"
                                .'Davide';

                            // Genera l'email con GPT-4o
                            try {
                                $payloadEmail = [
                                    'model' => 'gpt-4o',
                                    'messages' => [
                                        ['role' => 'system', 'content' => 'Sei un assistente che genera email basate su un prompt fornito.'],
                                        ['role' => 'user', 'content' => $finalPrompt],
                                    ],
                                    'max_tokens' => 1024,
                                ];
                                $apiResponseEmail = $client->post($openaiEndpoint, [
                                    'headers' => [
                                        'Authorization' => 'Bearer '.$openaiApiKey,
                                        'Content-Type'  => 'application/json',
                                    ],
                                    'json' => $payloadEmail,
                                ]);
                                $apiResultEmail = json_decode($apiResponseEmail->getBody()->getContents(), true);
                                $emailContent = $apiResultEmail['choices'][0]['message']['content'] ?? 'Nessun risultato';
                                $emailContent = nl2br($emailContent);
                            } catch (\Exception $e) {
                                continue;
                            }

                            // Invia l'email, aggiorna lo status e logga il messaggio
                            try {
                                Mail::html($emailContent, function ($message) use ($record) {
                                    $message->to($record->email)
                                        ->subject('C’è una cosa interessante nel tuo sito internet.');
                                });
                                $record->update(['status' => 'in_contact']);
                                SentMessage::create([
                                    'message' => $emailContent,
                                    'contact' => $record->email,
                                    'type'    => 'email',
                                ]);

                                // Notifica per ogni email inviata con successo
                                Notification::make()
                                    ->title('Successo')
                                    ->body('Email inviata correttamente a '.$record->email)
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                // Notifica in caso di errore per l'email specifica
                                Notification::make()
                                    ->title('Errore')
                                    ->body('Errore nell\'invio dell\'email a '.$record->email.': '.$e->getMessage())
                                    ->danger()
                                    ->send();
                                continue;
                            }
                        }

                        Notification::make()
                            ->title('Successo')
                            ->body('Email inviate per i record selezionati.')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->modalHeading('Invio Email Bulk')
                    ->modalSubheading('Inserisci il prompt per generare le email per i record selezionati.'),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
