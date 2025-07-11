<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\ProductionOrderResource\Pages;
use App\Filament\Resources\ProductionOrderResource\RelationManagers\ProductionPhasesRelationManager;
use App\Models\ProductionOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductionOrderResource extends Resource
{
    protected static ?string $model = ProductionOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $modelLabel = 'Ordine di Produzione';

    protected static ?string $pluralModelLabel = 'Ordini di Produzione';

    protected static ?string $navigationGroup = 'Produzione';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer')
                    ->label('Cliente')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('order_date')
                    ->label('Data Ordine')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->options(OrderStatus::class)
                    ->required(),
                Forms\Components\Select::make('bom_id')
                    ->label('Distinta Base')
                    ->relationship('bom', 'product_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Ordine')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bom.product_name')
                    ->label('Prodotto (Distinta Base)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Data Ordine')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filtra per Stato')
                    ->options(OrderStatus::class)
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
            ProductionPhasesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionOrders::route('/'),
            'create' => Pages\CreateProductionOrder::route('/create'),
            'edit' => Pages\EditProductionOrder::route('/{record}/edit'),
        ];
    }
}
