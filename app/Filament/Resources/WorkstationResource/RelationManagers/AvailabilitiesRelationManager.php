<?php

namespace App\Filament\Resources\WorkstationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AvailabilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'availabilities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('day_of_week')
                    ->label('Giorno della Settimana')
                    ->options([
                        'lunedi' => 'Lunedì',
                        'martedi' => 'Martedì',
                        'mercoledi' => 'Mercoledì',
                        'giovedi' => 'Giovedì',
                        'venerdi' => 'Venerdì',
                        'sabato' => 'Sabato',
                        'domenica' => 'Domenica',
                    ]),
                Forms\Components\TimePicker::make('start_time')
                    ->label('Orario Inizio')
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->label('Orario Fine')
                    ->required(),
                Forms\Components\Toggle::make('is_available')
                    ->label('Disponibile')
                    ->default(true),
                Forms\Components\DatePicker::make('exception_date')
                    ->label('Data Eccezione'),
                Forms\Components\Select::make('type')
                    ->options([
                        'regular' => 'Regolare',
                        'exception' => 'Eccezione',
                        'holiday' => 'Festività',
                    ])->default('regular'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day_of_week')
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')->label('Giorno'),
                Tables\Columns\TextColumn::make('start_time')->label('Inizio'),
                Tables\Columns\TextColumn::make('end_time')->label('Fine'),
                Tables\Columns\IconColumn::make('is_available')->label('Disponibile')->boolean(),
                Tables\Columns\TextColumn::make('exception_date')->label('Data Eccezione')->date(),
                Tables\Columns\TextColumn::make('type')->label('Tipo'),
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
