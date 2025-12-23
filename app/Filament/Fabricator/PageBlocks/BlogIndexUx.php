<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Article;
use App\Models\Tag;
use Filament\Forms\Components\Builder\Block;
use Illuminate\Contracts\View\View;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class BlogIndexUx extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('blog-index-ux')
            ->label('Blog Index (UX)')
            ->schema([
                // Nessun campo: stile e layout fissi per coerenza UX.
            ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function mutateData(array $data): array
    {
        $selectedTag = request('tag');
        $searchQuery = request('search');

        $query = Article::with('featuredImage', 'tags')
            ->orderBy('created_at', 'desc');

        if ($selectedTag) {
            $query->whereHas('tags', function ($q) use ($selectedTag) {
                $q->where('tags.id', $selectedTag);
            });
        }

        if ($searchQuery) {
            $query->where('title', 'like', '%'.$searchQuery.'%');
        }

        // UX: abbastanza card da dare scelta, ma senza "scroll infinito" a colpo d'occhio
        $data['rows'] = $query->paginate(6);
        $data['tags'] = Tag::orderBy('name')->get();
        $data['selectedTag'] = $selectedTag;

        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.blog-index-ux');
    }
}
