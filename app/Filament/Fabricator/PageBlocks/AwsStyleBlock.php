<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Illuminate\Contracts\View\View;

class AwsStyleBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('aws-style-block')
            ->label('Blocco Stile AWS')
            ->schema([
                // Sezione "Perché AWS?"
                Section::make('sezione_perche')
                    ->label('Sezione "Perché?"')
                    ->schema([
                        TextInput::make('perche_title')
                            ->label('Titolo principale')
                            ->default('Perché AWS?')
                            ->required(),
                        
                        RichEditor::make('perche_description')
                            ->label('Descrizione')
                            ->default('AWS è la piattaforma cloud più completa e ampiamente utilizzata al mondo, con milioni di clienti attivi che utilizzano centinaia di servizi per ridurre i costi, diventare più agili e innovare più velocemente.')
                            ->required()
                            ->columnSpanFull(),
                        
                        // Repeater per gli accordion
                        Repeater::make('accordion_items')
                            ->label('Elementi Accordion')
                            ->schema([
                                Select::make('icon')
                                    ->label('Icona')
                                    ->options([
                                        'heroicon-o-server' => 'Server',
                                        'heroicon-o-cloud' => 'Cloud',
                                        'heroicon-o-shield-check' => 'Scudo Sicurezza',
                                        'heroicon-o-lightning-bolt' => 'Lampo',
                                        'heroicon-o-globe-alt' => 'Globo',
                                        'heroicon-o-cog' => 'Ingranaggio',
                                        'heroicon-o-chart-bar' => 'Grafico',
                                        'heroicon-o-database' => 'Database',
                                        'heroicon-o-network' => 'Rete',
                                        'heroicon-o-cpu-chip' => 'CPU',
                                        'heroicon-o-key' => 'Chiave',
                                        'heroicon-o-lock-closed' => 'Lucchetto',
                                        'heroicon-o-rocket-launch' => 'Razzo',
                                        'heroicon-o-star' => 'Stella',
                                        'heroicon-o-check-circle' => 'Check Cerchio',
                                        'heroicon-o-users' => 'Utenti',
                                        'heroicon-o-building-office' => 'Edificio',
                                        'heroicon-o-wrench-screwdriver' => 'Cacciavite',
                                        'heroicon-o-beaker' => 'Beaker',
                                        'heroicon-o-code-bracket' => 'Codice',
                                    ])
                                    ->default('heroicon-o-server')
                                    ->searchable()
                                    ->required(),
                                
                                TextInput::make('title')
                                    ->label('Titolo elemento')
                                    ->required(),
                                RichEditor::make('content')
                                    ->label('Contenuto elemento')
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->maxItems(10)
                            ->defaultItems(5)
                            ->default([
                                [
                                    'icon' => 'heroicon-o-server',
                                    'title' => 'Il più ampio e approfondito set di funzionalità cloud',
                                    'content' => 'AWS offre oltre 200 servizi cloud completi da data center globali. Che tu abbia bisogno di calcolo, storage, database, networking, analytics, machine learning, intelligenza artificiale, IoT, mobile, sicurezza, hybrid, media, o applicazioni, AWS ha i servizi per aiutarti a muoverti più velocemente, ridurre i costi IT e scalare le tue applicazioni.',
                                ],
                                [
                                    'icon' => 'heroicon-o-users',
                                    'title' => 'La più grande community di clienti e partner',
                                    'content' => 'Milioni di clienti attivi utilizzano AWS ogni mese in oltre 190 paesi. La community AWS include startup in rapida crescita, grandi aziende e agenzie governative leader. I partner AWS includono migliaia di system integrators che si specializzano in servizi AWS e decine di migliaia di rivenditori indipendenti di software che rendono la loro tecnologia disponibile su AWS.',
                                ],
                                [
                                    'icon' => 'heroicon-o-shield-check',
                                    'title' => 'Sicurezza sulla quale poter contare',
                                    'content' => 'AWS è progettato per essere la piattaforma cloud più flessibile e sicura disponibile oggi. La nostra infrastruttura core è costruita per soddisfare i requisiti di sicurezza per militari, banche globali e altre organizzazioni altamente sensibili. Questo è supportato da un ampio set di servizi di sicurezza cloud che ti aiutano a soddisfare i tuoi requisiti di sicurezza.',
                                ],
                                [
                                    'icon' => 'heroicon-o-rocket-launch',
                                    'title' => 'Innovazione che accelera la trasformazione',
                                    'content' => 'AWS ha costantemente innovato dal 2006, introducendo servizi che definiscono il cloud computing. AWS ha più servizi e più funzionalità all\'interno di quei servizi rispetto a qualsiasi altro provider cloud, inclusi calcolo, storage, database, networking, data lake e analytics, machine learning e intelligenza artificiale, IoT, mobile, sicurezza, hybrid, media, e applicazioni.',
                                ],
                                [
                                    'icon' => 'heroicon-o-chart-bar',
                                    'title' => 'Maggiore esperienza operativa comprovata',
                                    'content' => 'AWS ha più esperienza operativa a livello globale rispetto a qualsiasi altro provider cloud, con milioni di clienti attivi che utilizzano centinaia di milioni di istanze AWS ogni mese. AWS ha la più grande community di clienti e partner, con migliaia di system integrators che si specializzano in servizi AWS e centinaia di migliaia di rivenditori indipendenti di software.',
                                ],
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

    }

    public static function mutateData(array $data): array
    {
        return $data;
    }


} 