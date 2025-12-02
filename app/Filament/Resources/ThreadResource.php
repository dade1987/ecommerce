<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThreadResource\Pages;
use App\Models\Thread;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use function Safe\json_encode;

class ThreadResource extends Resource
{
    protected static ?string $model = Thread::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('thread_id')
                    ->label('Thread ID')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('team_slug')
                    ->label('Team')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('activity_uuid')
                    ->label('Activity UUID')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Textarea::make('user_agent')
                    ->label('User Agent')
                    ->rows(3)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('url')
                    ->label('URL')
                    ->rows(2)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('cookies')
                    ->label('Cookies')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('headers')
                    ->label('Headers')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('server_params')
                    ->label('Server Params (JSON)')
                    ->formatStateUsing(fn ($state) => $state !== null ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '')
                    ->rows(10)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('thread_id')
                    ->label('Thread ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('team_slug')
                    ->label('Team')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Messaggi')
                    ->counts('messages')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListThreads::route('/'),
            'view' => Pages\ViewThread::route('/{record}'),
        ];
    }
}
