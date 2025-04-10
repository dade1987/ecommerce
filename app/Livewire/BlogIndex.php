<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Article;
use App\Models\Tag;

class BlogIndex extends Component
{
    public $title;
    public $rows;
    public $selectedTag = '';
    public $tags;

    public function mount($title = 'Blog')
    {
        $this->title = $title;
        $this->tags = Tag::orderBy('name')->get();
        $this->loadArticles();
    }

    public function updatedSelectedTag()
    {
        $this->loadArticles();
    }

    protected function loadArticles()
    {
        $query = Article::with('featuredImage', 'tags')
            ->orderBy('created_at', 'desc');

        if ($this->selectedTag) {
            $query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->selectedTag);
            });
        }

        $this->rows = $query->get();
    }

    public function render()
    {
        return view('livewire.blog-index');
    }
}
