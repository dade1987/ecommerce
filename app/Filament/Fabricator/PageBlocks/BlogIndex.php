<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Article;
use App\Models\Tag;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class BlogIndex extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('blog-index')
            ->schema([
                TextInput::make('title')->default('Blog'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        $selectedTag = request('tag');

        $query = Article::with('featuredImage', 'tags')
            ->orderBy('created_at', 'desc');

        if ($selectedTag) {
            $query->whereHas('tags', function ($q) use ($selectedTag) {
                $q->where('tags.id', $selectedTag);
            });
        }

        $data['rows'] = $query->paginate(3);
        $data['tags'] = Tag::orderBy('name')->get();
        $data['selectedTag'] = $selectedTag;

        return $data;
    }
}
