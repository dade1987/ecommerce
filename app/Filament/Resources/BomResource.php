<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BomResource\Pages;
use App\Models\Bom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BomResource extends Resource
{
    protected static ?string $model = Bom::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $modelLabel = 'Distinta Base';

    protected static ?string $pluralModelLabel = 'Distinte Basi';

    protected static ?string $navigationGroup = 'Produzione';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('internal_code')
                    ->label('Codice Interno')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Repeater::make('materials')
                    ->label('Materiali')
                    ->schema([
                        Forms\Components\TextInput::make('material_type')
                            ->label('Materiale (es. Lamiera Acciaio)')
                            ->required(),
                        Forms\Components\TextInput::make('thickness')
                            ->label('Spessore (mm)')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantità')
                            ->integer()
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('internal_code')
                    ->label('Codice Interno')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Creazione')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Data Aggiornamento')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoms::route('/'),
            'create' => Pages\CreateBom::route('/create'),
            'edit' => Pages\EditBom::route('/{record}/edit'),
        ];
    }
}
