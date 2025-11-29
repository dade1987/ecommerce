<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
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
                Section::make('Configurazione Base')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titolo')
                            ->placeholder('Parla con il nostro assistente'),

                        TextInput::make('model_url')
                            ->label('URL Modello 3D')
                            ->placeholder('/avatar3d/models/avatar.glb')
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
                    ]),

                Section::make('Posizionamento e Layout')
                    ->schema([
                        Toggle::make('fixed_position')
                            ->label('Posizione Fixed (in basso)')
                            ->default(true)
                            ->helperText('Posiziona l\'avatar fixed in basso a destra'),

                        Toggle::make('transparent_background')
                            ->label('Sfondo Trasparente')
                            ->default(true)
                            ->helperText('Rende lo sfondo del canvas trasparente'),

                        TextInput::make('height')
                            ->label('Altezza')
                            ->placeholder('80vh')
                            ->default('80vh')
                            ->helperText('Altezza del container (es. 400px, 80vh)'),

                        TextInput::make('aspect_ratio')
                            ->label('Rapporto Larghezza/Altezza')
                            ->numeric()
                            ->placeholder('0.75')
                            ->default(0.75)
                            ->helperText('0.75 = 3:4, 1 = quadrato'),

                        TextInput::make('position_bottom')
                            ->label('Offset dal basso')
                            ->placeholder('0')
                            ->default('0')
                            ->helperText('Distanza dal basso (es. 20px)'),

                        TextInput::make('position_right')
                            ->label('Offset da destra')
                            ->placeholder('0')
                            ->default('0')
                            ->helperText('Distanza da destra (es. 20px)'),

                        Select::make('avatar_view')
                            ->label('Inquadratura Avatar')
                            ->options([
                                'bust' => 'Busto (3/4)',
                                'full' => 'Figura intera',
                            ])
                            ->default('full')
                            ->helperText('Quanto dell\'avatar mostrare'),
                    ]),

                Section::make('Controlli e Debug')
                    ->schema([
                        Select::make('orbit_controls')
                            ->label('Controlli Orbitali')
                            ->options([
                                'none' => 'Disabilitati',
                                'limited' => 'Limitati (zoom/rotazione con limiti)',
                                'debug' => 'Debug (libertà totale)',
                            ])
                            ->default('none')
                            ->helperText('Permette zoom e rotazione della camera'),

                        Toggle::make('show_leva_panel')
                            ->label('Mostra Pannello Leva')
                            ->default(false)
                            ->helperText('Mostra pannello controlli bones'),

                        Toggle::make('enable_bone_controls')
                            ->label('Abilita Controlli Bones')
                            ->default(false)
                            ->helperText('Abilita slider per debug bones'),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function mutateData(array $data): array
    {
        // Default values per i booleani
        $booleanDefaults = [
            'fixed_position' => true,
            'transparent_background' => true,
            'show_leva_panel' => false,
            'enable_bone_controls' => false,
            'enable_speech_recognition' => true,
            'enable_chat' => true,
        ];

        // Cast boolean values explicitly (Toggle può salvare come 1/0/null/string)
        foreach ($booleanDefaults as $field => $default) {
            if (isset($data[$field])) {
                $data[$field] = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN);
            } else {
                $data[$field] = $default;
            }
        }

        // Default per altri campi
        $data['height'] = $data['height'] ?? '80vh';
        $data['avatar_view'] = $data['avatar_view'] ?? 'full';
        $data['aspect_ratio'] = $data['aspect_ratio'] ?? 0.75;
        $data['orbit_controls'] = $data['orbit_controls'] ?? 'none';

        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.avatar-3d-react');
    }
}