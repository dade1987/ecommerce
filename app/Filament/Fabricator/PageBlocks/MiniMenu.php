<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Menu;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\View\View;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class MiniMenu extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('mini-menu')
            ->label('Mini Menu (sotto)')
            ->schema([
                Select::make('menu_name')
                    ->label('Menu')
                    ->options(fn (): array => Menu::query()->orderBy('name')->pluck('name', 'name')->toArray())
                    ->searchable()
                    ->required(),
                TextInput::make('title')
                    ->label('Titolo (opzionale)')
                    ->placeholder('Es: Scopri anche…'),
            ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function mutateData(array $data): array
    {
        $menuName = isset($data['menu_name']) && is_string($data['menu_name']) ? $data['menu_name'] : '';
        $menu = $menuName !== '' ? Menu::query()->where('name', $menuName)->first() : null;

        $data['items'] = $menu
            ? $menu->items()->orderBy('sort')->get()
            : collect();

        $data['title'] = isset($data['title']) && is_string($data['title']) ? trim($data['title']) : '';

        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.mini-menu');
    }
}
