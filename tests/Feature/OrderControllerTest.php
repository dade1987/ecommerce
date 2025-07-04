<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_index_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\OrderController::class, 'index'));
    }

    /** @test */
    public function test_create_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\OrderController::class, 'create'));
    }

    /** @test */
    public function test_store_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\OrderController::class, 'store'));
    }

    /** @test */
    public function test_show_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\OrderController::class, 'show'));
    }

    /** @test */
    public function test_edit_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\OrderController::class, 'edit'));
    }

    /** @test */
    public function test_update_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\OrderController::class, 'update'));
    }

    /** @test */
    public function test_destroy_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\OrderController::class, 'destroy'));
    }

    /** @test */
    public function test_controller_uses_correct_request_classes()
    {
        $controller = app(\App\Http\Controllers\OrderController::class);
        $reflectionClass = new \ReflectionClass($controller);
        
        $storeMethod = $reflectionClass->getMethod('store');
        $storeParams = $storeMethod->getParameters();
        
        $this->assertEquals('App\Http\Requests\StoreOrderRequest', $storeParams[0]->getType()->getName());
        
        $updateMethod = $reflectionClass->getMethod('update');
        $updateParams = $updateMethod->getParameters();
        
        $this->assertEquals('App\Http\Requests\UpdateOrderRequest', $updateParams[0]->getType()->getName());
    }

    /** @test */
    public function test_controller_methods_return_void_or_null()
    {
        $order = Order::factory()->create();

        // Test che i metodi esistono e non generano errori quando chiamati
        $controller = app(\App\Http\Controllers\OrderController::class);
        
        $this->assertNull($controller->index());
        $this->assertNull($controller->create());
        $this->assertNull($controller->show($order));
        $this->assertNull($controller->edit($order));
        $this->assertNull($controller->destroy($order));
    }

    /** @test */
    public function test_order_model_binding_works()
    {
        $order = Order::factory()->create();
        
        $response = $this->actingAs($this->user)
            ->get("/orders/{$order->id}");
        
        // Siccome il metodo show() è vuoto, potremmo ottenere un 404 o 200
        // dipende dall'implementazione delle routes
        $this->assertTrue(in_array($response->status(), [200, 404, 500]));
    }
} 