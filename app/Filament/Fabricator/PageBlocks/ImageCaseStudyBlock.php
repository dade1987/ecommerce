<?php

namespace App\Filament\Fabricator\PageBlocks;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Models\Media;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class ImageCaseStudyBlock extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('image-case-study-block')
            ->schema([
                Select::make('alignment')
                    ->label('Allineamento Immagini')
                    ->options([
                        'left' => 'Sinistra',
                        'right' => 'Destra',
                    ])
                    ->default('left')
                    ->required(),
                TextInput::make('title')
                    ->label('Titolo')
                    ->required(),
                TextInput::make('subtitle')
                    ->label('Sottotitolo'),
                RichEditor::make('description')
                    ->label('Descrizione')
                    ->required(),
                CuratorPicker::make('images')
                    ->label('Galleria Immagini')
                    ->multiple()
                    ->required(),
            ]);
    }

    public static function getLayout(): string
    {
        return 'image-case-study-block';
    }

    public static function mutateData(array $data): array
    {
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = Media::whereIn('id', $data['images'])
                ->get()
                ->sortBy(function ($media) use ($data) {
                    return array_search($media->id, $data['images']);
                });
        }

        return $data;
    }
} 