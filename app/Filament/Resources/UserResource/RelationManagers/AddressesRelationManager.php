<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nation'),
                Forms\Components\TextInput::make('region'),
                Forms\Components\TextInput::make('province'),
                Forms\Components\TextInput::make('municipality'),
                Forms\Components\TextInput::make('street'),
                Forms\Components\TextInput::make('postal_code'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('municipality')
            ->columns([
                Tables\Columns\TextColumn::make('nation'),
                Tables\Columns\TextColumn::make('region'),
                Tables\Columns\TextColumn::make('province'),
                Tables\Columns\TextColumn::make('municipality'),
                Tables\Columns\TextColumn::make('street'),
                Tables\Columns\TextColumn::make('postal_code'),
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
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
