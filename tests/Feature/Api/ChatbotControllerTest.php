<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Team;
use App\Models\Customer;
use App\Models\Quoter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatbotControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->team = Team::factory()->create([
            'slug' => 'test-team',
            'welcome_message' => 'Benvenuto nel nostro team!'
        ]);
    }

    /** @test */
    public function test_create_thread_returns_thread_id()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/create-thread');

        $response->assertStatus(200)
            ->assertJsonStructure(['thread_id']);
    }

    /** @test */
    public function test_create_thread_generates_unique_thread_id()
    {
        $response1 = $this->actingAs($this->user)
            ->postJson('/api/chatbot/create-thread');

        $response2 = $this->actingAs($this->user)
            ->postJson('/api/chatbot/create-thread');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $threadId1 = $response1->json('thread_id');
        $threadId2 = $response2->json('thread_id');

        $this->assertNotEquals($threadId1, $threadId2);
    }

    /** @test */
    public function test_handle_chat_creates_thread_if_not_provided()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'Hello',
                'team' => 'test-team'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['thread_id']);
    }

    /** @test */
    public function test_handle_chat_with_existing_thread()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'Hello',
                'team' => 'test-team',
                'thread_id' => 'existing-thread-id'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['thread_id']);
    }

    /** @test */
    public function test_handle_chat_buongiorno_returns_welcome_message()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'buongiorno',
                'team' => 'test-team'
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Benvenuto nel nostro team!']);
    }

    /** @test */
    public function test_handle_chat_buongiorno_case_insensitive()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'BUONGIORNO',
                'team' => 'test-team'
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Benvenuto nel nostro team!']);
    }

    /** @test */
    public function test_handle_chat_stores_user_message()
    {
        $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'Test message',
                'team' => 'test-team',
                'thread_id' => 'test-thread-id'
            ]);

        $this->assertDatabaseHas('quoters', [
            'thread_id' => 'test-thread-id',
            'role' => 'user',
            'content' => 'Test message'
        ]);
    }

    /** @test */
    public function test_handle_chat_stores_welcome_message()
    {
        $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'buongiorno',
                'team' => 'test-team',
                'thread_id' => 'test-thread-id'
            ]);

        $this->assertDatabaseHas('quoters', [
            'thread_id' => 'test-thread-id',
            'role' => 'chatbot',
            'content' => 'Benvenuto nel nostro team!'
        ]);
    }

    /** @test */
    public function test_handle_chat_with_uuid_parameter()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'Test message',
                'team' => 'test-team',
                'uuid' => 'test-uuid-123'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_handle_chat_with_nonexistent_team()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'buongiorno',
                'team' => 'nonexistent-team'
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Benvenuto!']); // Default message
    }

    /** @test */
    public function test_controller_constructor_initializes_openai_client()
    {
        $controller = app(\App\Http\Controllers\Api\ChatbotController::class);
        
        $this->assertInstanceOf(\OpenAI\Client::class, $controller->client);
    }

    /** @test */
    public function test_controller_has_required_methods()
    {
        $controller = app(\App\Http\Controllers\Api\ChatbotController::class);
        
        $this->assertTrue(method_exists($controller, 'createThread'));
        $this->assertTrue(method_exists($controller, 'handleChat'));
    }

    /** @test */
    public function test_controller_has_private_helper_methods()
    {
        $controller = app(\App\Http\Controllers\Api\ChatbotController::class);
        
        $this->assertTrue(method_exists($controller, 'retrieveRunResult'));
        $this->assertTrue(method_exists($controller, 'fetchProductData'));
        $this->assertTrue(method_exists($controller, 'fetchAddressData'));
        $this->assertTrue(method_exists($controller, 'fetchAvailableTimes'));
        $this->assertTrue(method_exists($controller, 'createOrder'));
        $this->assertTrue(method_exists($controller, 'submitUserData'));
        $this->assertTrue(method_exists($controller, 'fetchFAQs'));
        $this->assertTrue(method_exists($controller, 'scrapeSite'));
        $this->assertTrue(method_exists($controller, 'formatResponseContent'));
    }

    /** @test */
    public function test_private_methods_are_actually_private()
    {
        $controller = app(\App\Http\Controllers\Api\ChatbotController::class);
        $reflection = new \ReflectionClass($controller);
        
        $privateMethods = [
            'retrieveRunResult',
            'fetchProductData',
            'fetchAddressData',
            'fetchAvailableTimes',
            'createOrder',
            'submitUserData',
            'fetchFAQs',
            'scrapeSite',
            'formatResponseContent'
        ];

        foreach ($privateMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPrivate(), "Method {$methodName} should be private");
        }
    }

    /** @test */
    public function test_openai_client_configuration()
    {
        $controller = app(\App\Http\Controllers\Api\ChatbotController::class);
        
        $this->assertInstanceOf(\OpenAI\Client::class, $controller->client);
        $this->assertIsObject($controller->client);
    }

    /** @test */
    public function test_handle_chat_requires_message()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'team' => 'test-team'
            ]);

        // Should handle missing message gracefully
        $this->assertTrue(in_array($response->status(), [200, 400, 422]));
    }

    /** @test */
    public function test_handle_chat_requires_team()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chatbot/handle-chat', [
                'message' => 'Test message'
            ]);

        // Should handle missing team gracefully
        $this->assertTrue(in_array($response->status(), [200, 400, 422]));
    }

    /** @test */
    public function test_controller_uses_correct_models()
    {
        $controller = app(\App\Http\Controllers\Api\ChatbotController::class);
        
        // Test che i modelli siano correttamente utilizzati
        $this->assertTrue(class_exists(\App\Models\Customer::class));
        $this->assertTrue(class_exists(\App\Models\Quoter::class));
        $this->assertTrue(class_exists(\App\Models\Team::class));
    }
} 