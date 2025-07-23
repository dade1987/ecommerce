<?php

namespace App\Filament\Resources\ProductionLineResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkstationsRelationManager extends RelationManager
{
    protected static string $relationship = 'workstations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament-production.Nome Postazione'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('filament-production.Descrizione'))
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label(__('filament-production.Stato'))
                    ->options([
                        'active' => __('filament-production.Attiva'),
                        'inactive' => __('filament-production.Inattiva'),
                        'maintenance' => __('filament-production.In Manutenzione'),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('capacity')
                    ->label(__('filament-production.Capacità (ore/giorno)'))
                    ->required()
                    ->numeric()
                    ->default(8),
                Forms\Components\TextInput::make('batch_size')
                    ->label(__('filament-production.Dimensione Lotto (unità)'))
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\TextInput::make('time_per_unit')
                    ->label(__('filament-production.Tempo per Unità (minuti)'))
                    ->numeric()
                    ->required()
                    ->default(10)
                    ->minValue(1),
                Forms\Components\Select::make('real_time_status')
                    ->label(__('filament-production.Stato Real-Time'))
                    ->options([
                        'running' => __('filament-production.In Funzione'),
                        'idle' => __('filament-production.Inattiva'),
                        'faulted' => __('filament-production.Guasta'),
                    ])
                    ->default('idle')
                    ->required(),
                Forms\Components\TextInput::make('current_speed')
                    ->label(__('filament-production.Velocità Corrente (unità/ora)'))
                    ->numeric(),
                Forms\Components\TextInput::make('wear_level')
                    ->label(__('filament-production.Livello Usura (%)'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('error_rate')
                    ->label(__('filament-production.Tasso di Errore (%)'))
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\DateTimePicker::make('last_maintenance_date')
                    ->label(__('filament-production.Data Ultima Manutenzione')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-production.Nome Postazione'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('filament-production.Descrizione')),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament-production.Stato'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'maintenance' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('filament-production.Capacità'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch_size')
                    ->label(__('filament-production.Dimensione Lotto'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_per_unit')
                    ->label(__('filament-production.Tempo per Unità (min)'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('real_time_status')
                    ->label(__('filament-production.Stato Real-Time'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'running' => 'success',
                        'idle' => 'warning',
                        'faulted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('current_speed')
                    ->label(__('filament-production.Velocità (u/h)'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('wear_level')
                    ->label(__('filament-production.Usura (%)'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('error_rate')
                    ->label(__('filament-production.Tasso Errore (%)'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_maintenance_date')
                    ->label(__('filament-production.Data Ultima Manutenzione'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
