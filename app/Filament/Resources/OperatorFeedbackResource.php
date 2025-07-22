<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperatorFeedbackResource\Pages;
use App\Models\OperatorFeedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OperatorFeedbackResource extends Resource
{
    protected static ?string $model = OperatorFeedback::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
public static function getNavigationGroup(): string
    {
        return __('filament-logistics.Logistica');
    }

public static function getModelLabel(): string
    {
        return __('filament-logistics.Feedback Operatore');
    }

public static function getPluralModelLabel(): string
    {
        return __('filament-logistics.Feedback Operatori');
    }
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('titolo')
                    ->label(__('filament-logistics.Titolo'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('descrizione')
                    ->label(__('filament-logistics.Descrizione'))
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->label(__('filament-logistics.Stato'))
                    ->required()
                    ->options([
                        'pending' => __('filament-logistics.In Attesa'),
                        'in_progress' => __('filament-logistics.In Corso'),
                        'done' => __('filament-logistics.Completato'),
                        'rejected' => __('filament-logistics.Rifiutato'),
                    ])
                    ->default('pending')
                    ->columnSpan(1),

                Forms\Components\KeyValue::make('metadata')
                    ->label(__('filament-logistics.Metadati'))
                    ->helperText('Dati aggiuntivi in formato chiave-valore per estensioni future')
                    ->columnSpanFull()
                    ->collapsed(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titolo')
                    ->label(__('filament-logistics.Titolo'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('filament-logistics.Stato'))
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'done',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('filament-logistics.In Attesa'),
                        'in_progress' => __('filament-logistics.In Corso'),
                        'done' => __('filament-logistics.Completato'),
                        'rejected' => __('filament-logistics.Rifiutato'),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('descrizione')
                    ->label(__('filament-logistics.Descrizione'))
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 100) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-logistics.Creato il'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament-logistics.Aggiornato il'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament-logistics.Stato'))
                    ->options([
                        'pending' => __('filament-logistics.In Attesa'),
                        'in_progress' => __('filament-logistics.In Corso'),
                        'done' => __('filament-logistics.Completato'),
                        'rejected' => __('filament-logistics.Rifiutato'),
                    ]),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListOperatorFeedback::route('/'),
            'create' => Pages\CreateOperatorFeedback::route('/create'),
            'edit' => Pages\EditOperatorFeedback::route('/{record}/edit'),
        ];
    }
}
