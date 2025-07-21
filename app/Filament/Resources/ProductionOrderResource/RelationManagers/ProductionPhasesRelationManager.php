<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use App\Models\Workstation;

class ProductionPhasesRelationManager extends RelationManager
{
    protected static string $relationship = 'phases';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Fasi di Produzione';

    protected static ?string $modelLabel = 'Fase';

    protected static ?string $pluralModelLabel = 'Fasi';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome Fase')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('workstation_id')
                    ->label('Postazione di Lavoro')
                    ->options(Workstation::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('estimated_duration')
                    ->label('Durata Stimata (minuti)')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('setup_time')
                    ->label('Tempo di Setup (minuti)')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\Toggle::make('is_maintenance')
                    ->label('Fase di Manutenzione')
                    ->inline(false),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('workstation.name')->label('Postazione'),
                Tables\Columns\TextColumn::make('estimated_duration')->label('Durata Stim. (min)'),
                Tables\Columns\TextColumn::make('setup_time')
                    ->label('Setup (min)')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_maintenance')
                    ->label('Manutenzione')
                    ->boolean(),
                Tables\Columns\TextColumn::make('scheduled_start_time')->label('Inizio Pianificato')->dateTime(),
                Tables\Columns\TextColumn::make('scheduled_end_time')->label('Fine Pianificata')->dateTime(),
                Tables\Columns\TextColumn::make('start_time')->label('Inizio Effettivo')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('end_time')->label('Fine Effettiva')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_completed')->label('Completata')->boolean(),
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