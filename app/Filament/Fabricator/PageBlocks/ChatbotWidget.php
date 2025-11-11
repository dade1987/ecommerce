<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class ChatbotWidget extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('chatbot-widget')
            ->schema([
                TextInput::make('team_slug')
                    ->label('Team Slug')
                    ->required()
                    ->helperText('Inserisci lo slug del team per collegare il chatbot corretto.'),
                Select::make('prompt_type')
                    ->label('Tipo di Chat')
                    ->options([
                        'business' => 'Chat Aziendale (prodotti, servizi, prenotazioni)',
                        'chat_libera' => 'Chat Libera (assistente generico)',
                    ])
                    ->default('business')
                    ->required()
                    ->helperText('Seleziona il tipo di assistente da utilizzare.'),
            ]);
    }

    public static function getLabel(): string
    {
        return 'Chatbot Widget';
    }
} 