<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogisticProductResource\Pages;
use App\Models\LogisticProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LogisticProductResource extends Resource
{
    protected static ?string $model = LogisticProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Logistica';
    protected static ?string $modelLabel = 'Prodotto Logistico';
    protected static ?string $pluralModelLabel = 'Prodotti Logistici';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codice')
                    ->label('Codice Prodotto')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('nome')
                    ->label('Nome Prodotto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('unita_misura')
                    ->label('Unità di Misura')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('es. kg, pz, litri')
                    ->columnSpan(1),

                Forms\Components\Textarea::make('descrizione')
                    ->label('Descrizione')
                    ->columnSpanFull()
                    ->rows(3),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codice')
                    ->label('Codice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unita_misura')
                    ->label('Unità di Misura')
                    ->sortable(),

                Tables\Columns\TextColumn::make('descrizione')
                    ->label('Descrizione')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unita_misura')
                    ->label('Unità di Misura')
                    ->options(function () {
                        return LogisticProduct::distinct()
                            ->pluck('unita_misura', 'unita_misura')
                            ->toArray();
                    }),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogisticProducts::route('/'),
            'create' => Pages\CreateLogisticProduct::route('/create'),
            'edit' => Pages\EditLogisticProduct::route('/{record}/edit'),
        ];
    }
}
