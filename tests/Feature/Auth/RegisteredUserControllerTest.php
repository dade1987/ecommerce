<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function test_create_returns_register_view()
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
            ->assertViewIs('auth.register');
    }

    /** @test */
    public function test_store_creates_new_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertRedirect('/dashboard'); // Assuming RouteServiceProvider::HOME is '/dashboard'

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function test_store_hashes_password()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotEquals('password123', $user->password);
    }

    /** @test */
    public function test_store_fires_registered_event()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);

        Event::assertDispatched(Registered::class, function ($event) {
            return $event->user->email === 'john@example.com';
        });
    }

    /** @test */
    public function test_store_automatically_logs_in_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function test_store_redirects_to_home()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertRedirect('/dashboard'); // Assuming RouteServiceProvider::HOME
    }

    /** @test */
    public function test_store_validates_required_fields()
    {
        $response = $this->post('/register', []);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function test_store_validates_name_required()
    {
        $userData = [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function test_store_validates_name_string()
    {
        $userData = [
            'name' => 123,
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function test_store_validates_name_max_length()
    {
        $userData = [
            'name' => str_repeat('a', 256), // 256 characters
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function test_store_validates_email_required()
    {
        $userData = [
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function test_store_validates_email_format()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function test_store_validates_email_unique()
    {
        $existingUser = User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function test_store_validates_password_required()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function test_store_validates_password_confirmation()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function test_store_validates_password_rules()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123', // Too short
            'password_confirmation' => '123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function test_guest_can_access_register_page()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_authenticated_user_cannot_access_register_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/register');

        // Should redirect authenticated users away from register page
        $response->assertStatus(302);
    }

    /** @test */
    public function test_store_throws_validation_exception_on_invalid_data()
    {
        $userData = [
            'name' => '', // Invalid
            'email' => 'invalid-email', // Invalid
            'password' => '123', // Too short
            'password_confirmation' => '456', // Doesn't match
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }
} 