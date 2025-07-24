<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FidelityCardResource\Pages;
use App\Filament\Resources\FidelityCardResource\RelationManagers;
use App\Models\FidelityCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class FidelityCardResource extends Resource
{
    protected static ?string $model = FidelityCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('card_number')
                    ->label('Card Number')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->default(fn () => Str::upper(Str::random(16))),
                Forms\Components\TextInput::make('points')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'expired' => 'Expired',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\DateTimePicker::make('expires_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('card_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListFidelityCards::route('/'),
            'create' => Pages\CreateFidelityCard::route('/create'),
            'edit' => Pages\EditFidelityCard::route('/{record}/edit'),
        ];
    }
}
