<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
                    ->label('Nome Fase (es. Taglio, Foratura)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('start_time')
                    ->label('Inizio Lavorazione'),
                Forms\Components\DateTimePicker::make('end_time')
                    ->label('Fine Lavorazione'),
                Forms\Components\TextInput::make('operator')
                    ->label('Operatore')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_completed')
                    ->label('Fase Completata'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Fase'),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Inizio')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fine')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('operator')
                    ->label('Operatore'),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Completata')
                    ->boolean(),
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