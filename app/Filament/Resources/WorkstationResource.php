<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkstationResource\Pages;
use App\Filament\Resources\WorkstationResource\RelationManagers;
use App\Filament\Resources\WorkstationResource\RelationManagers\AvailabilitiesRelationManager;
use App\Models\Workstation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkstationResource extends Resource
{
    protected static ?string $model = Workstation::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Produzione';

    protected static ?string $modelLabel = 'Postazione di Lavoro';

    protected static ?string $pluralModelLabel = 'Postazioni di Lavoro';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('production_line_id')
                    ->label('Linea di Produzione')
                    ->relationship('productionLine', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nome Postazione')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descrizione')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->options([
                        'active' => 'Attiva',
                        'inactive' => 'Inattiva',
                        'maintenance' => 'In Manutenzione',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('capacity')
                    ->label('Capacità (ore/giorno)')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome Postazione')
                    ->searchable(),
                Tables\Columns\TextColumn::make('productionLine.name')
                    ->label('Linea di Produzione')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'maintenance' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacità')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Creazione')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
