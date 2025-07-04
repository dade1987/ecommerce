<?php

namespace Tests\Feature;

use App\Models\GuttenbergPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GuttenbergControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_invoke_method_exists()
    {
        $this->assertTrue(method_exists(\App\Http\Controllers\GuttenbergController::class, '__invoke'));
    }

    /** @test */
    public function test_invoke_returns_view_with_page()
    {
        $page = GuttenbergPage::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/gutenberg/{$page->id}");

        $response->assertStatus(200)
            ->assertViewIs('guttenberg-example')
            ->assertViewHas('page', $page);
    }

    /** @test */
    public function test_invoke_handles_non_existent_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/gutenberg/999');

        $response->assertStatus(200)
            ->assertViewIs('guttenberg-example')
            ->assertViewHas('page', null);
    }

    /** @test */
    public function test_invoke_accepts_id_parameter()
    {
        $controller = app(\App\Http\Controllers\GuttenbergController::class);
        $reflection = new \ReflectionClass($controller);
        
        $invokeMethod = $reflection->getMethod('__invoke');
        $parameters = $invokeMethod->getParameters();
        
        $this->assertCount(2, $parameters);
        $this->assertEquals('id', $parameters[0]->getName());
        $this->assertEquals('int', $parameters[0]->getType()->getName());
        $this->assertEquals('request', $parameters[1]->getName());
        $this->assertEquals('Illuminate\Http\Request', $parameters[1]->getType()->getName());
    }

    /** @test */
    public function test_invoke_uses_gutenberg_page_model()
    {
        $page = GuttenbergPage::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/gutenberg/{$page->id}");

        $response->assertStatus(200);
        $viewPage = $response->viewData('page');
        $this->assertInstanceOf(GuttenbergPage::class, $viewPage);
    }

    /** @test */
    public function test_invoke_is_invokable_controller()
    {
        $controller = app(\App\Http\Controllers\GuttenbergController::class);
        
        $this->assertTrue(is_callable($controller));
    }

    /** @test */
    public function test_invoke_finds_page_by_id()
    {
        $page = GuttenbergPage::factory()->create(['id' => 123]);

        $response = $this->actingAs($this->user)
            ->get('/gutenberg/123');

        $response->assertStatus(200);
        $viewPage = $response->viewData('page');
        $this->assertEquals(123, $viewPage->id);
    }

    /** @test */
    public function test_invoke_returns_guttenberg_example_view()
    {
        $page = GuttenbergPage::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/gutenberg/{$page->id}");

        $response->assertStatus(200)
            ->assertViewIs('guttenberg-example');
    }

    /** @test */
    public function test_controller_extends_base_controller()
    {
        $controller = app(\App\Http\Controllers\GuttenbergController::class);
        
        $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);
    }

    /** @test */
    public function test_controller_imports_correct_classes()
    {
        $controller = app(\App\Http\Controllers\GuttenbergController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('use App\Models\GuttenbergPage;', $source);
        $this->assertStringContainsString('use Illuminate\Http\Request;', $source);
    }

    /** @test */
    public function test_invoke_handles_request_parameter()
    {
        $page = GuttenbergPage::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/gutenberg/{$page->id}?param=value");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_invoke_accepts_post_requests()
    {
        $page = GuttenbergPage::factory()->create();

        $response = $this->actingAs($this->user)
            ->post("/gutenberg/{$page->id}", ['data' => 'test']);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_invoke_comment_indicates_invokable()
    {
        $controller = app(\App\Http\Controllers\GuttenbergController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('Handle the incoming request', $source);
    }

    /** @test */
    public function test_invoke_uses_find_method()
    {
        $controller = app(\App\Http\Controllers\GuttenbergController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('GuttenbergPage::find($id)', $source);
    }

    /** @test */
    public function test_invoke_compacts_page_variable()
    {
        $controller = app(\App\Http\Controllers\GuttenbergController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('compact(\'page\')', $source);
    }
} 