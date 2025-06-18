<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Tag;

class ArticleTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $smartTag = Tag::firstOrCreate(
            ['slug' => 'articolo-smart'],
            ['name' => 'Articolo Smart']
        );

        $articlesWithoutTags = Article::whereDoesntHave('tags')->get();

        foreach ($articlesWithoutTags as $article) {
            $article->tags()->attach($smartTag->id);
        }
    }
} 