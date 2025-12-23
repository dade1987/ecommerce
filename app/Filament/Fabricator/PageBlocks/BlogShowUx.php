<?php

namespace App\Filament\Fabricator\PageBlocks;

use App\Models\Article;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\View\View;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class BlogShowUx extends PageBlock
{
    public static function getBlockSchema(): Block
    {
        return Block::make('blog-show-ux')
            ->label('Blog Show (UX)')
            ->schema([
                Checkbox::make('compact_mode')
                    ->label('Modalità compatta (riduce lo scroll)')
                    ->helperText('Mostra un riepilogo e rende il contenuto completo espandibile (senza JS).')
                    ->default(true),
                TextInput::make('cta_primary_label')
                    ->label('CTA primaria (opzionale)')
                    ->placeholder('Es: Prova Interpreter ora')
                    ->default('Prenota una Call'),
            ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function mutateData(array $data): array
    {
        $param = (string) (request()->route('item0') ?? '');

        $article = Article::where('slug', $param)->first()
            ?? (is_numeric($param) ? Article::find((int) $param) : null);

        if (! $article) {
            $article = Article::findOrFail($param);
        }

        $data['row'] = $article;

        \Illuminate\Support\Facades\View::share('pageTitle', $article->title);
        \Illuminate\Support\Facades\View::share('pageDescription', $article->summary);
        \Illuminate\Support\Facades\View::share('ogImage', $article->featuredImage?->path);

        $data['compact_mode'] = (bool) ($data['compact_mode'] ?? true);
        $label = $data['cta_primary_label'] ?? 'Prenota una Call';
        $data['cta_primary_label'] = is_string($label) ? trim($label) : 'Prenota una Call';

        return $data;
    }

    public function render(): View
    {
        return view('components.filament-fabricator.page-blocks.blog-show-ux');
    }
}
