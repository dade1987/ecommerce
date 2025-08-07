<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class CaseStudyBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('case-study-block')
            ->schema([
                Select::make('alignment')
                    ->label('Allineamento Card')
                    ->options([
                        'left' => 'Sinistra',
                        'right' => 'Destra',
                    ])
                    ->default('left')
                    ->required(),
                TextInput::make('card_header_color_class')->label('Classe Colore Header Card (es. bg-primary)')->required(),
                TextInput::make('card_header_icon')->label('Icona Header Card (es. bi bi-shop)')->required(),
                TextInput::make('card_header_title')->label('Titolo Header Card')->required(),
                TextInput::make('card_header_subtitle')->label('Sottotitolo Header Card')->required(),

                TextInput::make('mockup_color_class')->label('Classe Colore Mockup (es. bg-gradient-primary)')->required(),
                TextInput::make('mockup_icon')->label('Icona Mockup (es. bi bi-laptop)')->required(),
                TextInput::make('mockup_title')->label('Titolo Mockup')->required(),
                TextInput::make('mockup_text')->label('Testo Mockup')->required(),

                Textarea::make('problem_text')->label('Problema Risolto')->required(),

                Repeater::make('results')
                    ->label('Risultati Ottenuti')
                    ->schema([
                        TextInput::make('value')->label('Valore (es. +340%)')->required(),
                        TextInput::make('label')->label('Etichetta (es. Vendite Online)')->required(),
                        TextInput::make('color_class')->label('Classe Colore (es. text-success)')->required(),
                    ])
                    ->grid(3)
                    ->maxItems(3),

                TextInput::make('details_title')->label('Titolo Sezione Dettagli')->required(),

                Builder::make('details_builder')
                    ->label('Contenuto Dettagli')
                    ->blocks([
                        Block::make('technologies')
                            ->label('Tecnologie Implementate')
                            ->schema([
                                Repeater::make('tech_items')
                                    ->label('Lista Tecnologie')
                                    ->schema([
                                        TextInput::make('icon_bg_class')->label('Classe Sfondo Icona (es. bg-primary)')->required(),
                                        TextInput::make('icon')->label('Icona (es. bi bi-code-slash)')->required(),
                                        TextInput::make('title')->label('Titolo')->required(),
                                        TextInput::make('subtitle')->label('Sottotitolo')->required(),
                                    ])->grid(2)
                            ]),
                        Block::make('features')
                            ->label('Caratteristiche Uniche')
                            ->schema([
                                Repeater::make('feature_items')
                                    ->label('Lista Caratteristiche')
                                    ->schema([
                                        TextInput::make('icon')->label('Icona (es. bi bi-check-circle-fill)')->required(),
                                        TextInput::make('text')->label('Testo')->required(),
                                    ])
                            ]),
                        Block::make('accordion')
                            ->label('Moduli Accordion')
                            ->schema([
                                Repeater::make('accordion_items')
                                    ->label('Elementi Accordion')
                                    ->schema([
                                        TextInput::make('icon')->label('Icona (es. bi bi-calendar-check)')->required(),
                                        TextInput::make('title')->label('Titolo')->required(),
                                        Textarea::make('content')->label('Contenuto (un punto per riga)')->required(),
                                    ])
                            ]),
                        Block::make('timeline')
                            ->label('Fasi Timeline')
                            ->schema([
                                Repeater::make('timeline_items')
                                    ->label('Elementi Timeline')
                                    ->schema([
                                        TextInput::make('step')->label('Step (es. 1)')->required(),
                                        TextInput::make('color_class')->label('Classe Colore (es. bg-primary)')->required(),
                                        TextInput::make('title')->label('Titolo')->required(),
                                        TextInput::make('text')->label('Testo')->required(),
                                    ])
                            ]),
                    ])
            ]);
    }

    public static function getLayout(): string
    {
        return 'case-study-block';
    }
} 