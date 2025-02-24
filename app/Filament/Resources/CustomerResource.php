<?php

namespace App\Filament\Resources;

use App\Actions\SendBulkEmailAction;
use App\Actions\SentBulkEmailAction;
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
use Illuminate\Support\Facades\Log;
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
                Forms\Components\DateTimePicker::make('visited_at')
                    ->label('Data di visita')
                    ->nullable(),
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
                Tables\Columns\TextColumn::make('visited_at')
                    ->label('Data di visita')
                    ->dateTime()
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('customer_group_id')
                    ->label('Gruppo Cliente')
                    ->relationship('customerGroup', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
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
