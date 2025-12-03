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

                Section::make('Widget Mode')
                    ->schema([
                        Toggle::make('widget_mode')
                            ->label('Modalità Widget')
                            ->default(true)
                            ->helperText('Chat collassata con pulsante toggle (indipendente da sfondo trasparente)'),
                    ]),

                Section::make('Ombra a Terra')
                    ->schema([
                        Toggle::make('show_shadow')
                            ->label('Mostra Ombra')
                            ->default(false)
                            ->helperText('Ombra sotto i piedi (richiede sfondo NON trasparente)'),

                        Select::make('shadow_preset')
                            ->label('Preset Ombra')
                            ->options([
                                'soft' => 'Soft (morbida)',
                                'sharp' => 'Sharp (definita)',
                                'diffuse' => 'Diffuse (molto morbida)',
                                'fullBody' => 'Full Body (per figura intera)',
                            ])
                            ->default('fullBody'),

                        TextInput::make('shadow_opacity')
                            ->label('Opacità Ombra')
                            ->numeric()
                            ->placeholder('0.7')
                            ->helperText('0-1, lascia vuoto per usare preset'),

                        TextInput::make('shadow_blur')
                            ->label('Blur Ombra')
                            ->numeric()
                            ->placeholder('1.5')
                            ->helperText('Sfumatura, lascia vuoto per usare preset'),

                        TextInput::make('shadow_y')
                            ->label('Posizione Y Ombra')
                            ->numeric()
                            ->default(0)
                            ->helperText('Altezza terreno (0 = piedi avatar)'),
                    ])
                    ->collapsed(),

                Section::make('Mouse Tracking')
                    ->schema([
                        TextInput::make('mouse_tracking_radius')
                            ->label('Raggio Tracking (px)')
                            ->numeric()
                            ->placeholder('400')
                            ->helperText('Lascia vuoto per tracking su tutto viewport'),

                        TextInput::make('mouse_tracking_speed')
                            ->label('Velocità Tracking')
                            ->numeric()
                            ->placeholder('0.08')
                            ->default(0.08)
                            ->helperText('0.01-0.2 (più basso = più lento)'),
                    ])
                    ->collapsed(),

                Section::make('Controlli e Debug')
                    ->schema([
                        Select::make('orbit_controls')
                            ->label('Controlli Orbitali')
                            ->options([
                                'none' => 'Disabilitati',
                                'limited' => 'Limitati (zoom/rotazione con limiti)',
                                'debug' => 'Debug (libertà totale)',
                            ])
                            ->default('limited')
                            ->helperText('Permette zoom e rotazione della camera'),

                        Toggle::make('show_fps')
                            ->label('Mostra FPS')
                            ->default(false)
                            ->helperText('Mostra pannello statistiche performance'),

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
            'widget_mode' => true,
            'show_shadow' => false,
            'show_fps' => false,
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
        $data['orbit_controls'] = $data['orbit_controls'] ?? 'limited';
        $data['shadow_preset'] = $data['shadow_preset'] ?? 'fullBody';
        $data['shadow_y'] = $data['shadow_y'] ?? 0;
        $data['mouse_tracking_speed'] = $data['mouse_tracking_speed'] ?? 0.08;

        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.avatar-3d-react');
    }
}