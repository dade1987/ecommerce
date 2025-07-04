<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_edit_returns_profile_edit_view()
    {
        $response = $this->actingAs($this->user)
            ->get('/profile/edit');

        $response->assertStatus(200)
            ->assertViewIs('profile.edit')
            ->assertViewHas('user', $this->user);
    }

    /** @test */
    public function test_update_modifies_user_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->actingAs($this->user)
            ->patch('/profile', $updateData);

        $response->assertStatus(302)
            ->assertRedirect('/profile/edit')
            ->assertSessionHas('status', 'profile-updated');

        $this->user->refresh();
        $this->assertEquals('Updated Name', $this->user->name);
        $this->assertEquals('updated@example.com', $this->user->email);
    }

    /** @test */
    public function test_update_clears_email_verification_when_email_changes()
    {
        $this->user->email_verified_at = now();
        $this->user->save();

        $updateData = [
            'name' => $this->user->name,
            'email' => 'newemail@example.com',
        ];

        $response = $this->actingAs($this->user)
            ->patch('/profile', $updateData);

        $response->assertStatus(302)
            ->assertRedirect('/profile/edit')
            ->assertSessionHas('status', 'profile-updated');

        $this->user->refresh();
        $this->assertNull($this->user->email_verified_at);
    }

    /** @test */
    public function test_update_preserves_email_verification_when_email_unchanged()
    {
        $verifiedAt = now();
        $this->user->email_verified_at = $verifiedAt;
        $this->user->save();

        $updateData = [
            'name' => 'Updated Name',
            'email' => $this->user->email, // Same email
        ];

        $response = $this->actingAs($this->user)
            ->patch('/profile', $updateData);

        $response->assertStatus(302)
            ->assertRedirect('/profile/edit')
            ->assertSessionHas('status', 'profile-updated');

        $this->user->refresh();
        $this->assertEquals($verifiedAt->format('Y-m-d H:i:s'), $this->user->email_verified_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function test_update_validates_using_profile_update_request()
    {
        $controller = app(\App\Http\Controllers\ProfileController::class);
        $reflectionClass = new \ReflectionClass($controller);
        
        $updateMethod = $reflectionClass->getMethod('update');
        $updateParams = $updateMethod->getParameters();
        
        $this->assertEquals('App\Http\Requests\ProfileUpdateRequest', $updateParams[0]->getType()->getName());
    }

    /** @test */
    public function test_destroy_deletes_user_account()
    {
        $password = 'password123';
        $this->user->password = Hash::make($password);
        $this->user->save();

        $response = $this->actingAs($this->user)
            ->delete('/profile', [
                'password' => $password,
            ]);

        $response->assertStatus(302)
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    /** @test */
    public function test_destroy_validates_current_password()
    {
        $response = $this->actingAs($this->user)
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    /** @test */
    public function test_destroy_logs_out_user()
    {
        $password = 'password123';
        $this->user->password = Hash::make($password);
        $this->user->save();

        $response = $this->actingAs($this->user)
            ->delete('/profile', [
                'password' => $password,
            ]);

        $response->assertStatus(302);
        $this->assertGuest();
    }

    /** @test */
    public function test_destroy_invalidates_session()
    {
        $password = 'password123';
        $this->user->password = Hash::make($password);
        $this->user->save();

        $response = $this->actingAs($this->user)
            ->delete('/profile', [
                'password' => $password,
            ]);

        $response->assertStatus(302);
        
        // Verifica che la sessione sia stata invalidata
        $this->assertGuest();
    }

    /** @test */
    public function test_destroy_requires_password_field()
    {
        $response = $this->actingAs($this->user)
            ->delete('/profile', []);

        $response->assertStatus(302)
            ->assertSessionHasErrors('password');
    }

    /** @test */
    public function test_guest_cannot_access_profile_edit()
    {
        $response = $this->get('/profile/edit');

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function test_guest_cannot_update_profile()
    {
        $response = $this->patch('/profile', [
            'name' => 'Test Name',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function test_guest_cannot_delete_profile()
    {
        $response = $this->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }
} 