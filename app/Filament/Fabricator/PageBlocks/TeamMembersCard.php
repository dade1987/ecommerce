<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TeamMembersCard extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('team-members-card')
            ->schema([
                TextInput::make('title')
                    ->label('Title'),
                TextInput::make('text')
                    ->label('Text'),
                Section::make('persons')
                    ->schema([
                        Repeater::make('persons')
                            ->schema([
                                TextInput::make('image')
                                    ->label('Person Image'),
                                TextInput::make('name')
                                    ->label('Person Name'),
                                TextInput::make('role')
                                    ->label('Person Role'),
                                TextInput::make('text')
                                    ->label('Person Text'),
                            ])
                            ->minItems(1) // Numero minimo di elementi
                            ->maxItems(10) // Numero massimo di elementi
                            ->label('Add Person'), // Etichetta del pulsante per aggiungere un nuovo elemento
                    ])
                    ->collapsible(), // Opzionale: rende la sezione collassabile
            ]);
    }

    public static function mutateData(array $data): array
    {
        foreach ($data['persons'] as &$person) {
            $person['image'] = url('images/'.$person['image']);
        }

        //dd($data);
        return $data;
    }
}
