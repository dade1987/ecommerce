<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_create_returns_login_view()
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertViewIs('auth.login');
    }

    /** @test */
    public function test_store_authenticates_user_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password', // Default password in factory
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/dashboard'); // Assuming RouteServiceProvider::HOME is '/dashboard'

        $this->assertAuthenticatedAs($this->user);
    }

    /** @test */
    public function test_store_fails_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors();

        $this->assertGuest();
    }

    /** @test */
    public function test_store_regenerates_session()
    {
        $oldSession = session()->getId();

        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302);
        $this->assertAuthenticatedAs($this->user);
        
        // Session should be regenerated for security
        $this->assertNotEquals($oldSession, session()->getId());
    }

    /** @test */
    public function test_store_redirects_to_referrer_if_provided()
    {
        $referrer = '/custom-referrer';
        
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
            'referrer' => $referrer,
        ]);

        $response->assertStatus(302)
            ->assertRedirect($referrer);

        $this->assertAuthenticatedAs($this->user);
    }

    /** @test */
    public function test_store_validates_using_login_request()
    {
        $controller = app(\App\Http\Controllers\Auth\AuthenticatedSessionController::class);
        $reflectionClass = new \ReflectionClass($controller);
        
        $storeMethod = $reflectionClass->getMethod('store');
        $storeParams = $storeMethod->getParameters();
        
        $this->assertEquals('App\Http\Requests\Auth\LoginRequest', $storeParams[0]->getType()->getName());
    }

    /** @test */
    public function test_destroy_logs_out_user()
    {
        $response = $this->actingAs($this->user)
            ->delete('/logout');

        $response->assertStatus(302)
            ->assertRedirect('/');

        $this->assertGuest();
    }

    /** @test */
    public function test_destroy_invalidates_session()
    {
        $sessionId = session()->getId();

        $response = $this->actingAs($this->user)
            ->delete('/logout');

        $response->assertStatus(302);
        $this->assertGuest();
        
        // Session should be invalidated
        $this->assertNotEquals($sessionId, session()->getId());
    }

    /** @test */
    public function test_destroy_regenerates_token()
    {
        $token = session()->token();

        $response = $this->actingAs($this->user)
            ->delete('/logout');

        $response->assertStatus(302);
        $this->assertGuest();
        
        // Token should be regenerated
        $this->assertNotEquals($token, session()->token());
    }

    /** @test */
    public function test_destroy_redirects_to_home()
    {
        $response = $this->actingAs($this->user)
            ->delete('/logout');

        $response->assertStatus(302)
            ->assertRedirect('/');
    }

    /** @test */
    public function test_guest_can_access_login_page()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_guest_cannot_logout()
    {
        $response = $this->delete('/logout');

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function test_store_requires_email()
    {
        $response = $this->post('/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function test_store_requires_password()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function test_store_validates_email_format()
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function test_controller_uses_web_guard()
    {
        $response = $this->actingAs($this->user)
            ->delete('/logout');

        $response->assertStatus(302);
        $this->assertGuest();
        
        // Verify the web guard is used (not API guard)
        $this->assertFalse(Auth::guard('web')->check());
    }
} 