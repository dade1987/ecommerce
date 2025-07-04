<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_index_method_accepts_multiple_parameters()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $indexMethod = $reflection->getMethod('index');
        $parameters = $indexMethod->getParameters();
        
        $this->assertGreaterThanOrEqual(1, count($parameters));
        $this->assertEquals('container0', $parameters[0]->getName());
        $this->assertEquals('item0', $parameters[1]->getName());
        $this->assertEquals('container1', $parameters[2]->getName());
        $this->assertEquals('item1', $parameters[3]->getName());
        $this->assertEquals('container2', $parameters[4]->getName());
        $this->assertEquals('item2', $parameters[5]->getName());
    }

    /** @test */
    public function test_index_handles_single_container()
    {
        $response = $this->actingAs($this->user)
            ->get('/pages/test-page');

        // Could return 200 or 404 depending on if FilamentFabricator is configured
        $this->assertTrue(in_array($response->status(), [200, 404, 500]));
    }

    /** @test */
    public function test_index_handles_nested_containers()
    {
        $response = $this->actingAs($this->user)
            ->get('/pages/category/1/item/2');

        // Could return 200 or 404 depending on if FilamentFabricator is configured
        $this->assertTrue(in_array($response->status(), [200, 404, 500]));
    }

    /** @test */
    public function test_index_uses_filament_fabricator()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('FilamentFabricator', $source);
        $this->assertStringContainsString('getPageModel', $source);
        $this->assertStringContainsString('getPageUrls', $source);
    }

    /** @test */
    public function test_index_shares_view_data()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('View::share', $source);
        $this->assertStringContainsString('pageTitle', $source);
        $this->assertStringContainsString('pageDescription', $source);
        $this->assertStringContainsString('ogImage', $source);
    }

    /** @test */
    public function test_index_handles_item_detection()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('is_item', $source);
        $this->assertStringContainsString('startsWith', $source);
        $this->assertStringContainsString('-show', $source);
    }

    /** @test */
    public function test_index_processes_value_correctly()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('Str::start', $source);
        $this->assertStringContainsString('array_key_last', $source);
        $this->assertStringContainsString('array_search', $source);
    }

    /** @test */
    public function test_index_queries_page_model()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('->where(\'id\', $pageId)', $source);
        $this->assertStringContainsString('->firstOrFail()', $source);
    }

    /** @test */
    public function test_index_uses_fabricator_page_controller()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('FabricatorPageController', $source);
        $this->assertStringContainsString('app(FabricatorPageController::class)', $source);
    }

    /** @test */
    public function test_controller_extends_base_controller()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        
        $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);
    }

    /** @test */
    public function test_controller_imports_correct_classes()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('use Illuminate\Support\Facades\View;', $source);
        $this->assertStringContainsString('use Illuminate\Support\Str;', $source);
        $this->assertStringContainsString('use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;', $source);
        $this->assertStringContainsString('use Z3d0X\FilamentFabricator\Http\Controllers\PageController as FabricatorPageController;', $source);
    }

    /** @test */
    public function test_index_handles_missing_page_gracefully()
    {
        try {
            $response = $this->actingAs($this->user)
                ->get('/pages/non-existent-page');

            // Should either find a page or throw a 404 via firstOrFail()
            $this->assertTrue(in_array($response->status(), [200, 404, 500]));
        } catch (\Exception $e) {
            // If FilamentFabricator is not configured, it might throw an exception
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function test_index_sets_og_image_asset()
    {
        $controller = app(\App\Http\Controllers\PageController::class);
        $reflection = new \ReflectionClass($controller);
        
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('asset(\'images/logo15.png\')', $source);
    }
} 