<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Mail\AppointmentNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('appointment_date')
                    ->label('Data Appuntamento')
                    ->seconds(false)
                    ->required(),
                Forms\Components\TextInput::make('with_person')
                    ->label('Con chi')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Note')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('appointment_date')
            ->columns([
                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Data Appuntamento')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('with_person')
                    ->label('Con chi'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Note'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (Model $record) {
                        $record->load('customer');
                        $recipients = ['d.cavallini@cavalliniservice.com', 'g.florian@cavalliniservice.com'];
                        Mail::to($recipients)->send(new AppointmentNotification($record, false));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function (Model $record) {
                        $recipients = ['d.cavallini@cavalliniservice.com', 'g.florian@cavalliniservice.com'];
                        Mail::to($recipients)->send(new AppointmentNotification($record, true));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function (\Illuminate\Support\Collection $records) {
                            $recipients = ['d.cavallini@cavalliniservice.com', 'g.florian@cavalliniservice.com'];
                            foreach ($records as $record) {
                                Mail::to($recipients)->send(new AppointmentNotification($record, true));
                            }
                        }),
                ]),
            ]);
    }
}
