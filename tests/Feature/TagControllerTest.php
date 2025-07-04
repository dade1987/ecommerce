<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_index_returns_categories_view()
    {
        $categories = Category::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/tags');

        $response->assertStatus(200)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    /** @test */
    public function test_index_returns_all_categories()
    {
        $categories = Category::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/tags');

        $response->assertStatus(200);
        $viewCategories = $response->viewData('categories');
        $this->assertCount(3, $viewCategories);
    }

    /** @test */
    public function test_create_returns_create_view()
    {
        $response = $this->actingAs($this->user)
            ->get('/tags/create');

        $response->assertStatus(200)
            ->assertViewIs('categories.create');
    }

    /** @test */
    public function test_store_creates_new_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
        ];

        $response = $this->actingAs($this->user)
            ->post('/tags', $categoryData);

        $response->assertStatus(302)
            ->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', $categoryData);
    }

    /** @test */
    public function test_store_accepts_all_request_data()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'slug' => 'test-category',
            'color' => '#FF0000',
        ];

        $response = $this->actingAs($this->user)
            ->post('/tags', $categoryData);

        $response->assertStatus(302)
            ->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', $categoryData);
    }

    /** @test */
    public function test_show_returns_category_view()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/tags/{$category->id}");

        $response->assertStatus(200)
            ->assertViewIs('categories.show')
            ->assertViewHas('category', $category);
    }

    /** @test */
    public function test_show_handles_non_existent_category()
    {
        $response = $this->actingAs($this->user)
            ->get('/tags/999');

        $response->assertStatus(200)
            ->assertViewIs('categories.show')
            ->assertViewHas('category', null);
    }

    /** @test */
    public function test_edit_returns_edit_view()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/tags/{$category->id}/edit");

        $response->assertStatus(200)
            ->assertViewIs('categories.edit')
            ->assertViewHas('category', $category);
    }

    /** @test */
    public function test_edit_handles_non_existent_category()
    {
        $response = $this->actingAs($this->user)
            ->get('/tags/999/edit');

        $response->assertStatus(200)
            ->assertViewIs('categories.edit')
            ->assertViewHas('category', null);
    }

    /** @test */
    public function test_update_modifies_category()
    {
        $category = Category::factory()->create();
        $updateData = [
            'name' => 'Updated Category Name',
            'description' => 'Updated Description',
        ];

        $response = $this->actingAs($this->user)
            ->put("/tags/{$category->id}", $updateData);

        $response->assertStatus(302)
            ->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', $updateData);
    }

    /** @test */
    public function test_update_handles_non_existent_category()
    {
        $response = $this->actingAs($this->user)
            ->put('/tags/999', [
                'name' => 'Test Name',
                'description' => 'Test Description',
            ]);

        $response->assertStatus(302)
            ->assertRedirect('/categories');
    }

    /** @test */
    public function test_destroy_deletes_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/tags/{$category->id}");

        $response->assertStatus(302)
            ->assertRedirect('/categories');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function test_destroy_handles_non_existent_category()
    {
        $response = $this->actingAs($this->user)
            ->delete('/tags/999');

        $response->assertStatus(302)
            ->assertRedirect('/categories');
    }

    /** @test */
    public function test_controller_uses_category_model()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/tags/{$category->id}");

        $response->assertStatus(200);
        $viewCategory = $response->viewData('category');
        $this->assertInstanceOf(Category::class, $viewCategory);
    }

    /** @test */
    public function test_all_methods_redirect_to_categories_route()
    {
        $category = Category::factory()->create();

        // Test store redirect
        $response = $this->actingAs($this->user)
            ->post('/tags', ['name' => 'Test']);
        $response->assertRedirect('/categories');

        // Test update redirect
        $response = $this->actingAs($this->user)
            ->put("/tags/{$category->id}", ['name' => 'Updated']);
        $response->assertRedirect('/categories');

        // Test destroy redirect
        $response = $this->actingAs($this->user)
            ->delete("/tags/{$category->id}");
        $response->assertRedirect('/categories');
    }
} 