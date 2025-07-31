<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
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
            ]);
    }

    public static function getLabel(): string
    {
        return 'Chatbot Widget';
    }
} 