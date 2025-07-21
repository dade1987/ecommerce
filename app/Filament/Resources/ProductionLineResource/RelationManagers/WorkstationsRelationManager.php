<?php

namespace App\Filament\Resources\ProductionLineResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkstationsRelationManager extends RelationManager
{
    protected static string $relationship = 'workstations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('availabilities_summary')
                    ->label('Orari di Lavoro')
                    ->getStateUsing(function (Model $record) {
                        $availabilities = $record->availabilities()
                            ->where('type', 'regular')
                            ->orderBy('day_of_week') // Ensure correct order
                            ->get()
                            ->groupBy(fn($item) => $item->start_time . '-' . $item->end_time);

                        if ($availabilities->isEmpty()) {
                            return 'Nessun orario regolare definito';
                        }

                        return $availabilities->map(function ($group) {
                            $days = $group->pluck('day_of_week')->map(fn($day) => ucfirst(substr($day, 0, 3)))->join(', ');
                            $time = $group->first()->start_time . '-' . $group->first()->end_time;
                            return "{$days}: {$time}";
                        })->join(' | ');
                    }),
                Tables\Columns\TextColumn::make('capacity'),
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
