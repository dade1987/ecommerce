<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThreadResource\Pages;
use App\Models\Thread;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use function Safe\json_encode;

class ThreadResource extends Resource
{
    protected static ?string $model = Thread::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('ip_insights')
                    ->label('Info IP / Azienda')
                    ->content(function (?Thread $record): string {
                        if (! $record || ! $record->ip_address) {
                            return 'IP non disponibile per questo thread.';
                        }

                        return static::buildIpSummary($record->ip_address);
                    })
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('thread_id')
                    ->label('Thread ID')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('team_slug')
                    ->label('Team')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('activity_uuid')
                    ->label('Activity UUID')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Textarea::make('user_agent')
                    ->label('User Agent')
                    ->rows(3)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('url')
                    ->label('URL')
                    ->rows(2)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('cookies')
                    ->label('Cookies')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('headers')
                    ->label('Headers')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('server_params')
                    ->label('Server Params (JSON)')
                    ->formatStateUsing(fn ($state) => $state !== null ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '')
                    ->rows(10)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('thread_id')
                    ->label('Thread ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('team_slug')
                    ->label('Team')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Messaggi')
                    ->counts('messages')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThreads::route('/'),
            'view' => Pages\ViewThread::route('/{record}'),
        ];
    }

    /**
     * Recupera informazioni sull'IP (azienda, località, orario locale) usando un servizio pubblico.
     *
     * @param  string  $ip
     */
    protected static function buildIpSummary(string $ip): string
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return 'IP non valido.';
        }

        try {
            $response = Http::timeout(2)->get('http://ip-api.com/json/' . $ip, [
                'fields' => 'status,message,country,regionName,city,org,isp,timezone,query',
                'lang' => 'it',
            ]);

            if (! $response->successful()) {
                return 'Impossibile recuperare informazioni sull\'IP.';
            }

            /** @var array<string,mixed> $data */
            $data = $response->json();

            if (($data['status'] ?? 'fail') !== 'success') {
                return 'Impossibile recuperare informazioni sull\'IP.';
            }

            $org = $data['org'] ?? $data['isp'] ?? null;
            $city = $data['city'] ?? null;
            $region = $data['regionName'] ?? null;
            $country = $data['country'] ?? null;
            $timezone = $data['timezone'] ?? null;

            $parts = [];

            if ($org) {
                $parts[] = $org;
            }

            $location = collect([$city, $region, $country])->filter()->implode(', ');
            if ($location !== '') {
                $parts[] = $location;
            }

            if ($timezone) {
                try {
                    $localTime = Carbon::now($timezone)->format('d/m/Y H:i');
                    $parts[] = 'Ora locale: ' . $localTime . ' (' . $timezone . ')';
                } catch (\Throwable $e) {
                    // Se il timezone non è valido, ignoriamo l'orario
                }
            }

            if (empty($parts)) {
                return 'Nessuna informazione aggiuntiva disponibile per questo IP.';
            }

            return implode(' — ', $parts);
        } catch (\Throwable $e) {
            return 'Impossibile recuperare informazioni sull\'IP.';
        }
    }
}
