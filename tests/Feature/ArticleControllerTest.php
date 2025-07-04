<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    /** @test */
    public function test_index_returns_articles_with_tags_and_translations()
    {
        $articles = Article::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/articles');

        $response->assertStatus(200)
            ->assertViewIs('articles.index')
            ->assertViewHas('articles');
    }

    /** @test */
    public function test_create_returns_create_view_with_categories()
    {
        $categories = Category::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/articles/create');

        $response->assertStatus(200)
            ->assertViewIs('articles.create')
            ->assertViewHas('categories');
    }

    /** @test */
    public function test_store_creates_new_article()
    {
        $category = Category::factory()->create();
        $articleData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'category_id' => $category->id,
        ];

        $response = $this->actingAs($this->user)
            ->post('/articles', $articleData);

        $response->assertStatus(302)
            ->assertRedirect('/articles');

        $this->assertDatabaseHas('articles', [
            'title' => $articleData['title'],
            'content' => $articleData['content'],
        ]);
    }

    /** @test */
    public function test_store_creates_article_with_image()
    {
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('test.jpg');

        $articleData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'category_id' => $category->id,
            'image' => $image,
        ];

        $response = $this->actingAs($this->user)
            ->post('/articles', $articleData);

        $response->assertStatus(302)
            ->assertRedirect('/articles');

        $this->assertDatabaseHas('articles', [
            'title' => $articleData['title'],
            'content' => $articleData['content'],
        ]);

        $article = Article::where('title', $articleData['title'])->first();
        $this->assertNotNull($article->image_path);
        $this->assertTrue(Storage::disk('public')->exists($article->image_path));
    }

    /** @test */
    public function test_show_returns_article_with_tags_and_translations()
    {
        $article = Article::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertViewIs('articles.show')
            ->assertViewHas('article');
    }

    /** @test */
    public function test_edit_returns_edit_view_with_article_and_categories()
    {
        $article = Article::factory()->create();
        $categories = Category::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get("/articles/{$article->id}/edit");

        $response->assertStatus(200)
            ->assertViewIs('articles.edit')
            ->assertViewHas('article', $article)
            ->assertViewHas('categories');
    }

    /** @test */
    public function test_update_modifies_article()
    {
        $article = Article::factory()->create();
        $updateData = [
            'title' => 'Updated Article Title',
            'content' => 'Updated content for the article',
        ];

        $response = $this->actingAs($this->user)
            ->put("/articles/{$article->id}", $updateData);

        $response->assertStatus(302)
            ->assertRedirect('/articles');

        $this->assertDatabaseHas('articles', $updateData);
    }

    /** @test */
    public function test_update_modifies_article_with_new_image()
    {
        $article = Article::factory()->create();
        $newImage = UploadedFile::fake()->image('new-image.jpg');

        $updateData = [
            'title' => 'Updated Article Title',
            'content' => 'Updated content for the article',
            'image' => $newImage,
        ];

        $response = $this->actingAs($this->user)
            ->put("/articles/{$article->id}", $updateData);

        $response->assertStatus(302)
            ->assertRedirect('/articles');

        $article->refresh();
        $this->assertEquals('Updated Article Title', $article->title);
        $this->assertNotNull($article->image_path);
        $this->assertTrue(Storage::disk('public')->exists($article->image_path));
    }

    /** @test */
    public function test_destroy_deletes_article()
    {
        $article = Article::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/articles/{$article->id}");

        $response->assertStatus(302)
            ->assertRedirect('/articles');

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    /** @test */
    public function test_destroy_handles_non_existent_article()
    {
        $response = $this->actingAs($this->user)
            ->delete('/articles/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_show_handles_non_existent_article()
    {
        $response = $this->actingAs($this->user)
            ->get('/articles/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_edit_handles_non_existent_article()
    {
        $response = $this->actingAs($this->user)
            ->get('/articles/999/edit');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_update_handles_non_existent_article()
    {
        $response = $this->actingAs($this->user)
            ->put('/articles/999', [
                'title' => 'Test Title',
                'content' => 'Test Content',
            ]);

        $response->assertStatus(404);
    }
} 