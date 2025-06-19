<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Filament\Resources\FeedbackResource\RelationManagers;
use App\Models\Feedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\HeaderAction;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->disabled()
                    ->required(),
                Forms\Components\Textarea::make('feedback')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('extract_all_feedback')
                    ->label('Estrai Feedback per Copia')
                    ->form([
                        Textarea::make('all_feedback')
                            ->label('Feedback Estratti')
                            ->default(function () {
                                $feedbacks = Feedback::with('customer')->get();
                                return $feedbacks->map(function ($feedback) {
                                    return "Nome: " . ($feedback->customer->name ?? 'N/A') . "\n" .
                                           "Email: " . ($feedback->customer->email ?? 'N/A') . "\n" .
                                           "Feedback: " . $feedback->feedback . "\n" .
                                           "----------------------------------------";
                                })->implode("\n\n");
                            })
                            ->rows(20)
                            ->readOnly(),
                    ])
                    ->modalHeading('Tutti i Feedback')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Chiudi'),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('feedback')
                    ->limit(50)
                    ->wrap(),
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFeedback::route('/'),
        ];
    }
}
