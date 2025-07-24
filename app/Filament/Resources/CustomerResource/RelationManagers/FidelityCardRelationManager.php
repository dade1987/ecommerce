<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FidelityCardRelationManager extends RelationManager
{
    protected static string $relationship = 'fidelityCards';

    protected static ?string $recordTitleAttribute = 'card_number';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('card_number')
                    ->label('Numero Carta')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->default(fn () => Str::upper(Str::random(16))),
                Forms\Components\TextInput::make('points')
                    ->label('Punti')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->options([
                        'active' => 'Attiva',
                        'suspended' => 'Sospesa',
                        'expired' => 'Scaduta',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Data Scadenza'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('card_number')
            ->columns([
                Tables\Columns\TextColumn::make('card_number')
                    ->label('Numero Carta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('points')
                    ->label('Punti')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'expired' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Scadenza')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Creazione')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ultimo Aggiornamento')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'active' => 'Attiva',
                        'suspended' => 'Sospesa',
                        'expired' => 'Scaduta',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crea Fidelity Card'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifica'),
                Tables\Actions\DeleteAction::make()
                    ->label('Elimina'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Elimina Selezionate'),
                ]),
            ]);
    }
} 