<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();
    }

    /** @test */
    public function test_index_returns_categories_for_authorized_user()
    {
        $categories = Category::factory(3)->create(['is_hidden' => false]);

        $response = $this->actingAs($this->user)
            ->get('/categories');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json());
    }

    /** @test */
    public function test_index_returns_only_non_hidden_categories()
    {
        $visibleCategory = Category::factory()->create(['is_hidden' => false]);
        $hiddenCategory = Category::factory()->create(['is_hidden' => true]);

        $response = $this->actingAs($this->user)
            ->get('/categories');

        $response->assertStatus(200);
        $categories = $response->json();
        
        $this->assertCount(1, $categories);
        $this->assertEquals($visibleCategory->id, $categories[0]['id']);
    }

    /** @test */
    public function test_index_returns_categories_ordered_by_order_column()
    {
        $category1 = Category::factory()->create(['is_hidden' => false, 'order_column' => 2]);
        $category2 = Category::factory()->create(['is_hidden' => false, 'order_column' => 1]);
        $category3 = Category::factory()->create(['is_hidden' => false, 'order_column' => 3]);

        $response = $this->actingAs($this->user)
            ->get('/categories');

        $response->assertStatus(200);
        $categories = $response->json();
        
        $this->assertEquals($category2->id, $categories[0]['id']);
        $this->assertEquals($category1->id, $categories[1]['id']);
        $this->assertEquals($category3->id, $categories[2]['id']);
    }

    /** @test */
    public function test_index_returns_json_for_api_request()
    {
        $categories = Category::factory(3)->create(['is_hidden' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'is_hidden',
                    'order_column',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function test_index_denies_unauthorized_user_with_json_request()
    {
        $this->withoutExceptionHandling();

        $response = $this->actingAs($this->unauthorizedUser)
            ->getJson('/categories');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_index_denies_unauthorized_user_with_web_request()
    {
        $response = $this->actingAs($this->unauthorizedUser)
            ->get('/categories');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_index_denies_guest_user()
    {
        $response = $this->get('/categories');

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function test_create_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\CategoryController::class, 'create'));
    }

    /** @test */
    public function test_store_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\CategoryController::class, 'store'));
    }

    /** @test */
    public function test_show_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\CategoryController::class, 'show'));
    }

    /** @test */
    public function test_edit_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\CategoryController::class, 'edit'));
    }

    /** @test */
    public function test_update_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\CategoryController::class, 'update'));
    }

    /** @test */
    public function test_destroy_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\CategoryController::class, 'destroy'));
    }
} 