<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkstationResource\Pages;
use App\Filament\Resources\WorkstationResource\RelationManagers;
use App\Filament\Resources\WorkstationResource\RelationManagers\AvailabilitiesRelationManager;
use App\Models\Workstation;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkstationResource extends Resource
{
    protected static ?string $model = Workstation::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

public static function getNavigationGroup(): string
    {
        return __('filament-production.Produzione');
    }

public static function getModelLabel(): string
    {
        return __('filament-production.Postazione di Lavoro');
    }

public static function getPluralModelLabel(): string
    {
        return __('filament-production.Postazioni di Lavoro');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('production_line_id')
                    ->label(__('filament-production.Linea di Produzione'))
                    ->relationship('productionLine', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label(__('filament-production.Nome Postazione'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('filament-production.Descrizione'))
                    ->columnSpanFull(),
                Select::make('status')
                    ->label(__('filament-production.Stato'))
                    ->options([
                        'active' => __('filament-production.Attiva'),
                        'inactive' => __('filament-production.Inattiva'),
                        'maintenance' => __('filament-production.In Manutenzione'),
                    ])
                    ->required(),
                TextInput::make('capacity')
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
                Forms\Components\Section::make('Digital Twin Fields')
                    ->description(__('filament-production.Questi campi rappresentano lo stato in tempo reale della postazione.'))
                    ->columns(2)
                    ->schema([
                        Select::make('real_time_status')
                            ->options([
                                'running' => __('filament-production.In Funzione'),
                                'idle' => __('filament-production.Inattiva'),
                                'faulted' => __('filament-production.Guasta'),
                            ])
                            ->default('idle')
                            ->required(),
                        TextInput::make('current_speed')
                            ->label(__('filament-production.Velocità Corrente (unità/ora)'))
                            ->numeric(),
                        TextInput::make('wear_level')
                            ->label(__('filament-production.Livello Usura (%)'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->required(),
                        TextInput::make('error_rate')
                            ->label(__('filament-production.Tasso di Errore (%)'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        DateTimePicker::make('last_maintenance_date')
                            ->label(__('filament-production.Data Ultima Manutenzione')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-production.Nome Postazione'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('productionLine.name')
                    ->label(__('filament-production.Linea di Produzione'))
                    ->numeric()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('real_time_status')->label(__('filament-production.Stato Real-Time'))->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'running' => 'success',
                        'idle' => 'warning',
                        'faulted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('wear_level')->label(__('filament-production.Usura (%)'))->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('filament-production.Capacità'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-production.Data Creazione'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            AvailabilitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkstations::route('/'),
            'create' => Pages\CreateWorkstation::route('/create'),
            'edit' => Pages\EditWorkstation::route('/{record}/edit'),
        ];
    }
}
