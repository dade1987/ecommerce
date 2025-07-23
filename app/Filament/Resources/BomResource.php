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

    public static function getModelLabel(): string
    {
        return __('filament-production.Distinta Base');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-production.Distinte Basi');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament-production.Produzione');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('internal_product_id')
                    ->label(__('filament-production.Prodotto'))
                    ->options(\App\Models\InternalProduct::all()->mapWithKeys(fn($p) => [$p->id => $p->name . ' (' . $p->code . ')']))
                    ->required(),
                Forms\Components\TextInput::make('internal_code')
                    ->label(__('filament-production.Codice Interno'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Repeater::make('materials')
                    ->label(__('filament-production.Materiali'))
                    ->schema([
                        Forms\Components\TextInput::make('material_type')
                            ->label(__('filament-production.Materiale (es. Lamiera Acciaio)'))
                            ->required(),
                        Forms\Components\TextInput::make('thickness')
                            ->label(__('filament-production.Spessore (mm)'))
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('filament-production.Quantità'))
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
                Tables\Columns\TextColumn::make('internalProduct.name')
                    ->label(__('filament-production.Prodotto'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('internal_code')
                    ->label(__('filament-production.Codice Interno'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('materials')
                    ->label(__('filament-production.Materiali'))
                    ->formatStateUsing(fn($state) => is_array($state) ? collect($state)->pluck('material_type')->join(', ') : ''),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-production.Data Creazione'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament-production.Data Aggiornamento'))
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
