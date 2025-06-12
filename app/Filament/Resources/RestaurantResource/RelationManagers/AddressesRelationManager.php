<?php

namespace App\Filament\Resources\RestaurantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Geocomplete::make('location')
                    ->label('Cerca Indirizzo')
                    ->isLocation()
                    ->updateLatLng()
                    ->geocodeOnLoad()
                    ->columnSpanFull()
                    ->reverseGeocode([
                        'street'      => '%n %S',
                        'municipality'    => '%L',
                        'province'   => '%A2',
                        'region' => '%A1',
                        'nation'  => '%C',
                        'postal_code'   => '%z',
                    ]),
                Forms\Components\TextInput::make('street')->label('Via')->required(),
                Forms\Components\TextInput::make('municipality')->label('Comune')->required(),
                Forms\Components\TextInput::make('province')->label('Provincia')->required(),
                Forms\Components\TextInput::make('postal_code')->label('CAP')->required(),
                Forms\Components\TextInput::make('region')->label('Regione')->required(),
                Forms\Components\TextInput::make('nation')->label('Nazione')->required(),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('latitude')->required(),
                    Forms\Components\TextInput::make('longitude')->required(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street')
            ->columns([
                Tables\Columns\TextColumn::make('street')->label('Indirizzo'),
                Tables\Columns\TextColumn::make('municipality')->label('Comune'),
                Tables\Columns\TextColumn::make('province')->label('Provincia'),
                Tables\Columns\TextColumn::make('postal_code')->label('CAP'),
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
