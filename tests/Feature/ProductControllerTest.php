<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
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
    public function test_index_returns_products_for_authorized_user()
    {
        $products = Product::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/products');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json());
    }

    /** @test */
    public function test_index_returns_products_ordered_by_order_column()
    {
        $product1 = Product::factory()->create(['order_column' => 2]);
        $product2 = Product::factory()->create(['order_column' => 1]);
        $product3 = Product::factory()->create(['order_column' => 3]);

        $response = $this->actingAs($this->user)
            ->get('/products');

        $response->assertStatus(200);
        $products = $response->json();
        
        $this->assertEquals($product2->id, $products[0]['id']);
        $this->assertEquals($product1->id, $products[1]['id']);
        $this->assertEquals($product3->id, $products[2]['id']);
    }

    /** @test */
    public function test_index_with_category_filter()
    {
        $products = Product::factory(3)->create();
        $categoryId = 1;

        $response = $this->actingAs($this->user)
            ->get("/products?category_id={$categoryId}");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_index_returns_json_for_api_request()
    {
        $products = Product::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
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
            ->getJson('/products');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_index_denies_unauthorized_user_with_web_request()
    {
        $response = $this->actingAs($this->unauthorizedUser)
            ->get('/products');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_index_denies_guest_user()
    {
        $response = $this->get('/products');

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function test_create_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\ProductController::class, 'create'));
    }

    /** @test */
    public function test_store_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\ProductController::class, 'store'));
    }

    /** @test */
    public function test_show_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\ProductController::class, 'show'));
    }

    /** @test */
    public function test_edit_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\ProductController::class, 'edit'));
    }

    /** @test */
    public function test_update_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\ProductController::class, 'update'));
    }

    /** @test */
    public function test_destroy_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\ProductController::class, 'destroy'));
    }
} 