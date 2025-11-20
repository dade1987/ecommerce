<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use App\Models\Workstation;
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
                    ->label('Nome Fase')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('workstation_id')
                    ->label('Postazione di Lavoro')
                    ->options(Workstation::pluck('name', 'id'))
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
                Forms\Components\TextInput::make('actual_duration')
                    ->label('Durata Effettiva (minuti)')
                    ->numeric()
                    ->helperText('Compilare solo quando la fase è completata'),
                Forms\Components\DateTimePicker::make('scheduled_start_time')
                    ->label('Inizio Pianificato')
                    ->helperText('Orario di inizio pianificato per questa fase'),
                Forms\Components\DateTimePicker::make('scheduled_end_time')
                    ->label('Fine Pianificata')
                    ->helperText('Orario di fine pianificato per questa fase'),
                Forms\Components\DateTimePicker::make('start_time')
                    ->label('Inizio Effettivo')
                    ->helperText('Orario di inizio effettivo (compilare quando si inizia)'),
                Forms\Components\DateTimePicker::make('end_time')
                    ->label('Fine Effettiva')
                    ->helperText('Orario di fine effettivo (compilare quando si completa)'),
                Forms\Components\Toggle::make('is_maintenance')
                    ->label('Fase di Manutenzione')
                    ->inline(false),
                Forms\Components\Toggle::make('is_completed')
                    ->label('Fase Completata')
                    ->inline(false)
                    ->helperText('Segna come completata quando la fase è terminata'),
                Forms\Components\Textarea::make('notes')
                    ->label('Note')
                    ->rows(3)
                    ->maxLength(1000)
                    ->helperText('Note aggiuntive sulla fase di produzione'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome Fase')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('workstation.name')
                    ->label('Postazione')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_duration')
                    ->label('Durata Stim. (min)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('setup_time')
                    ->label('Setup (min)')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_maintenance')
                    ->label('Manutenzione')
                    ->boolean(),
                Tables\Columns\TextColumn::make('scheduled_start_time')
                    ->label('Inizio Pianificato')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_end_time')
                    ->label('Fine Pianificata')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Inizio Effettivo')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fine Effettiva')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Completata')
                    ->boolean(),
                Tables\Columns\TextColumn::make('actual_duration')
                    ->label('Durata Effettiva (min)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Note')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('workstation_id')
                    ->label('Postazione')
                    ->options(Workstation::pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_maintenance')
                    ->label('Solo Manutenzione'),
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('Solo Completate'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Modifica Fase di Produzione')
                    ->modalDescription('Modifica tutti i dettagli della fase di produzione'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_start_time', 'asc');
    }
}
