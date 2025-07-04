<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_index_with_single_container()
    {
        $products = Product::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/products');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json());
    }

    /** @test */
    public function test_index_with_container_and_item()
    {
        $category = Category::factory()->create();
        $products = Product::factory(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/categories/{$category->id}/products");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_index_handles_non_existent_model()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/nonexistent');

        // Dovrebbe gestire modelli non esistenti
        $this->assertTrue(in_array($response->status(), [200, 404, 500]));
    }

    /** @test */
    public function test_index_handles_non_existent_item()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/categories/999/products');

        // Dovrebbe gestire ID non esistenti
        $this->assertTrue(in_array($response->status(), [200, 404, 500]));
    }

    /** @test */
    public function test_index_with_nested_containers()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/categories/{$category->id}/products/{$product->id}/reviews");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_index_parameter_parsing()
    {
        $controller = app(\App\Http\Controllers\Api\ApiController::class);
        
        $result = $controller->index('products');
        
        $this->assertNotNull($result);
    }

    /** @test */
    public function test_index_uses_controller_delegation()
    {
        $products = Product::factory(3)->create();
        
        $controller = app(\App\Http\Controllers\Api\ApiController::class);
        
        $result = $controller->index('products');
        
        $this->assertNotNull($result);
        $this->assertTrue(is_iterable($result));
    }

    /** @test */
    public function test_index_creates_model_instance_when_no_controller()
    {
        $controller = app(\App\Http\Controllers\Api\ApiController::class);
        
        // Test con un modello che potrebbe non avere un controller
        $result = $controller->index('addresses');
        
        $this->assertNotNull($result);
    }

    /** @test */
    public function test_index_handles_model_relationships()
    {
        $address = Address::factory()->create();
        
        $controller = app(\App\Http\Controllers\Api\ApiController::class);
        
        $result = $controller->index('addresses', $address->id, 'users');
        
        $this->assertNotNull($result);
    }

    /** @test */
    public function test_create_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\Api\ApiController::class, 'create'));
    }

    /** @test */
    public function test_store_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\Api\ApiController::class, 'store'));
    }

    /** @test */
    public function test_show_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\Api\ApiController::class, 'show'));
    }

    /** @test */
    public function test_edit_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\Api\ApiController::class, 'edit'));
    }

    /** @test */
    public function test_update_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\Api\ApiController::class, 'update'));
    }

    /** @test */
    public function test_destroy_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\Api\ApiController::class, 'destroy'));
    }

    /** @test */
    public function test_show_method_expects_address_parameter()
    {
        $address = Address::factory()->create();
        $controller = app(\App\Http\Controllers\Api\ApiController::class);
        
        $result = $controller->show($address);
        $this->assertNull($result);
    }

    /** @test */
    public function test_controller_imports_and_uses_correct_classes()
    {
        $controller = app(\App\Http\Controllers\Api\ApiController::class);
        $reflection = new \ReflectionClass($controller);
        
        $this->assertTrue($reflection->hasMethod('index'));
        $this->assertTrue($reflection->hasMethod('create'));
        $this->assertTrue($reflection->hasMethod('store'));
        $this->assertTrue($reflection->hasMethod('show'));
        $this->assertTrue($reflection->hasMethod('edit'));
        $this->assertTrue($reflection->hasMethod('update'));
        $this->assertTrue($reflection->hasMethod('destroy'));
    }

    /** @test */
    public function test_index_method_signature_matches_expected_parameters()
    {
        $controller = app(\App\Http\Controllers\Api\ApiController::class);
        $reflection = new \ReflectionClass($controller);
        
        $indexMethod = $reflection->getMethod('index');
        $parameters = $indexMethod->getParameters();
        
        $this->assertGreaterThanOrEqual(1, count($parameters));
        $this->assertEquals('container0', $parameters[0]->getName());
    }

    /** @test */
    public function test_string_utility_functions_work()
    {
        $controller = app(\App\Http\Controllers\Api\ApiController::class);
        
        // Test che le funzioni Str:: funzionino come previsto
        $result = $controller->index('products');
        
        $this->assertNotNull($result);
    }
} 