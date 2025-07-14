<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomingEmailResource\Pages;
use App\Models\IncomingEmail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncomingEmailResource extends Resource
{
    protected static ?string $model = IncomingEmail::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Email Intelligenti';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('from_address')->disabled(),
                Forms\Components\TextInput::make('subject')->disabled(),
                Forms\Components\RichEditor::make('body_html')->disabled(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('from_address')->label('Mittente')->columnSpanFull(),
                TextEntry::make('subject')->label('Oggetto')->columnSpanFull(),
                TextEntry::make('received_at')->label('Ricevuta il')->dateTime()->columnSpanFull(),
                TextEntry::make('analysis')
                    ->label('Analisi AI')
                    ->columnSpanFull(),
                TextEntry::make('body_html')
                    ->label('Messaggio Originale')
                    ->html()
                    ->extraAttributes(['style' => 'max-height: 400px; overflow-y: auto; padding: 1rem; border: 1px solid #333; border-radius: 0.5rem;'])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_address')
                    ->label('Da')
                    ->searchable()
                    ->weight(fn (IncomingEmail $record): string => $record->is_read ? 'normal' : 'bold'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Oggetto')
                    ->searchable()
                    ->weight(fn (IncomingEmail $record): string => $record->is_read ? 'normal' : 'bold')
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priorità')
                    ->colors([
                        'danger' => fn ($state) => $state >= 8,
                        'warning' => fn ($state) => $state >= 5 && $state < 8,
                        'success' => fn ($state) => $state < 5,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('received_at')
                    ->label('Ricevuto il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->alignEnd(),
            ])
            ->defaultSort('received_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Lette')
                    ->boolean()
                    ->trueLabel('Solo lette')
                    ->falseLabel('Solo non lette')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListIncomingEmails::route('/'),
            // 'create' => Pages\CreateIncomingEmail::get(),
            // 'edit' => Pages\EditIncomingEmail::get(),
            'view' => Pages\ViewIncomingEmail::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('analysis')
            ->orderBy('priority', 'desc')
            ->orderBy('received_at', 'desc');
    }
}
