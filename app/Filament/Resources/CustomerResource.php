<?php

namespace App\Filament\Resources;

use App\Actions\SendBulkEmailAction;
use App\Actions\SendCustomHtmlEmailAction;
use App\Actions\SentBulkEmailAction;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\SentMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Safe\file_get_contents;
use function Safe\json_decode;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $isScopedToTenant = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['feedback', 'appointments', 'fidelityCards']);
    }

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
                        'discarded'      => 'Scartato',
                    ]),
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->disabled()
                    //->default(Str::uuid()->toString())
                    ->maxLength(36),
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
                                throw new \Exception('File non trovato: ' . $file);
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
                Tables\Columns\TextColumn::make('feedback_count')
                    ->label('Feedback')
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointments_count')
                    ->label('Appuntamenti')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fidelity_cards_count')
                    ->label('Fidelity Card')
                    ->sortable(),
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
                        'discarded'      => 'Scartato',
                    ]),
                Tables\Filters\TernaryFilter::make('has_feedback')
                    ->label('Ha Feedback')
                    ->boolean()
                    ->placeholder('Tutti')
                    ->trueLabel('Sì')
                    ->falseLabel('No')
                    ->query(function (Builder $query, $state): Builder {
                        if ($state === true) {
                            return $query->has('feedbacks');
                        }
                        if ($state === false) {
                            return $query->doesntHave('feedbacks');
                        }
                        return $query;
                    }),
                Tables\Filters\TernaryFilter::make('has_appointments')
                    ->label('Ha Appuntamenti')
                    ->boolean()
                    ->placeholder('Tutti')
                    ->trueLabel('Sì')
                    ->falseLabel('No')
                    ->query(function (Builder $query, $state): Builder {
                        if ($state === true) {
                            return $query->has('appointments');
                        }
                        if ($state === false) {
                            return $query->doesntHave('appointments');
                        }
                        return $query;
                    }),
                Tables\Filters\TernaryFilter::make('has_fidelity_cards')
                    ->label('Ha Fidelity Card')
                    ->boolean()
                    ->placeholder('Tutti')
                    ->trueLabel('Sì')
                    ->falseLabel('No')
                    ->query(function (Builder $query, $state): Builder {
                        if ($state === true) {
                            return $query->has('fidelityCards');
                        }
                        if ($state === false) {
                            return $query->doesntHave('fidelityCards');
                        }
                        return $query;
                    }),
                Tables\Filters\SelectFilter::make('customer_group_id')
                    ->label('Gruppo Cliente')
                    ->relationship('customerGroup', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\BulkAction::make('send_custom_html_email')
                    ->label('Invia Email HTML Personalizzata')
                    ->form([
                        Forms\Components\TextInput::make('email_subject')
                            ->label('Oggetto Email')
                            ->required(),
                        Forms\Components\RichEditor::make('email_content')
                            ->label('Contenuto Email (HTML)')
                            ->required(),
                    ])
                    ->action(function (array $data, \Illuminate\Support\Collection $records) {
                        foreach ($records as $record) {
                            app(SendCustomHtmlEmailAction::class)
                                ->onQueue() // mette l'azione in coda
                                ->execute($record, $data['email_subject'], $data['email_content']);
                        }
                        Notification::make()
                            ->title('Email inviate con successo!')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->modalHeading('Invia Email HTML Personalizzata')
                    ->modalSubheading('Inserisci il contenuto HTML dell\'email da inviare ai clienti selezionati.'),

                Tables\Actions\BulkAction::make('send_bulk_email')
                    ->label('Invia Email Bulk')
                    ->form([
                        Forms\Components\Textarea::make('email_prompt')
                            ->label('Prompt per Email')
                            ->default('Descrivi il prodotto in maniera amichevole, come se stessi scrivendo ad un amico.')
                            ->required(),
                    ])
                    ->action(function (array $data, \Illuminate\Support\Collection $records) {
                        app(SendBulkEmailAction::class)->onQueue()->execute($data, $records);
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
        return [
            RelationManagers\FeedbackRelationManager::class,
            RelationManagers\AppointmentsRelationManager::class,
            RelationManagers\FidelityCardRelationManager::class,
        ];
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
