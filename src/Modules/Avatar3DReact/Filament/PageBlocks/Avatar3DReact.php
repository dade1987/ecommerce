<?php

namespace Modules\Avatar3DReact\Filament\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;

class Avatar3DReact extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('avatar-3d-react')
            ->label('Avatar 3D React')
            ->icon('heroicon-o-cube')
            ->schema([
                TextInput::make('title')
                    ->label('Titolo')
                    ->placeholder('Parla con il nostro assistente'),

                TextInput::make('model_url')
                    ->label('URL Modello 3D')
                    ->placeholder('/models/avatar.glb')
                    ->helperText('Path al file .glb del modello 3D'),

                Select::make('voice')
                    ->label('Voce Azure')
                    ->options([
                        'it-IT-ElsaNeural' => 'Elsa (IT)',
                        'it-IT-IsabellaNeural' => 'Isabella (IT)',
                        'it-IT-DiegoNeural' => 'Diego (IT)',
                        'en-US-JennyNeural' => 'Jenny (EN)',
                        'en-US-GuyNeural' => 'Guy (EN)',
                    ])
                    ->default('it-IT-ElsaNeural'),

                Toggle::make('enable_speech_recognition')
                    ->label('Abilita riconoscimento vocale')
                    ->default(true),

                Toggle::make('enable_chat')
                    ->label('Abilita chat testuale')
                    ->default(true),
            ]);
    }

    public static function mutateData(array $data): array
    {
        // Add config values to data
        $data['azure_speech_key'] = config('avatar3d.azure.speech_key');
        $data['azure_speech_region'] = config('avatar3d.azure.speech_region');
        $data['openai_api_key'] = config('avatar3d.openai.api_key');

        return $data;
    }

    public static function render(array $data): View
    {
        return view('avatar3d::avatar-3d-react', ['data' => $data]);
    }
}