<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TeamMembersCard extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('team-members-card')
            ->schema([
                TextInput::make('title'),
                TextInput::make('text'),
                TextInput::make('person_one_image'),
                TextInput::make('person_one_name'),
                TextInput::make('person_one_role'),
                TextInput::make('person_one_text'),
                TextInput::make('person_two_image'),
                TextInput::make('person_two_name'),
                TextInput::make('person_two_role'),
                TextInput::make('person_two_text'),
                TextInput::make('person_three_image'),
                TextInput::make('person_three_name'),
                TextInput::make('person_three_role'),
                TextInput::make('person_three_text'),
                TextInput::make('person_four_image'),
                TextInput::make('person_four_name'),
                TextInput::make('person_four_role'),
                TextInput::make('person_four_text'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $data['person_one_image'] = url('images/'.$data['person_one_image']);
        $data['person_two_image'] = url('images/'.$data['person_two_image']);
        $data['person_three_image'] = url('images/'.$data['person_three_image']);
        $data['person_four_image'] = url('images/'.$data['person_four_image']);

        return $data;
    }
}
