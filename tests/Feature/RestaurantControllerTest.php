<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RestaurantControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_index_returns_all_restaurants()
    {
        $restaurants = Restaurant::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/restaurants');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function test_create_returns_create_view()
    {
        $response = $this->actingAs($this->user)
            ->get('/restaurants/create');

        $response->assertStatus(200)
            ->assertViewIs('restaurants.create');
    }

    /** @test */
    public function test_store_creates_new_restaurant()
    {
        $restaurantData = [
            'name' => $this->faker->company,
            'price_range' => '$$$',
            'phone_number' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'website' => $this->faker->url,
        ];

        $response = $this->actingAs($this->user)
            ->post('/restaurants', $restaurantData);

        $response->assertStatus(302)
            ->assertRedirect('/restaurants')
            ->assertSessionHas('success', 'Ristorante creato con successo');

        $this->assertDatabaseHas('restaurants', $restaurantData);
    }

    /** @test */
    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post('/restaurants', []);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function test_store_validates_email_format()
    {
        $restaurantData = [
            'name' => $this->faker->company,
            'email' => 'invalid-email',
        ];

        $response = $this->actingAs($this->user)
            ->post('/restaurants', $restaurantData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function test_store_validates_website_url()
    {
        $restaurantData = [
            'name' => $this->faker->company,
            'website' => 'invalid-url',
        ];

        $response = $this->actingAs($this->user)
            ->post('/restaurants', $restaurantData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['website']);
    }

    /** @test */
    public function test_show_returns_restaurant_view()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/restaurants/{$restaurant->id}");

        $response->assertStatus(200)
            ->assertViewIs('restaurants.show')
            ->assertViewHas('restaurant', $restaurant);
    }

    /** @test */
    public function test_edit_returns_edit_view()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/restaurants/{$restaurant->id}/edit");

        $response->assertStatus(200)
            ->assertViewIs('restaurants.edit')
            ->assertViewHas('restaurant', $restaurant);
    }

    /** @test */
    public function test_update_modifies_restaurant()
    {
        $restaurant = Restaurant::factory()->create();
        $updateData = [
            'name' => 'Updated Restaurant Name',
            'price_range' => '$$',
            'phone_number' => '123-456-7890',
            'email' => 'updated@example.com',
            'website' => 'https://updated.com',
        ];

        $response = $this->actingAs($this->user)
            ->put("/restaurants/{$restaurant->id}", $updateData);

        $response->assertStatus(302)
            ->assertRedirect('/restaurants')
            ->assertSessionHas('success', 'Ristorante aggiornato con successo');

        $this->assertDatabaseHas('restaurants', $updateData);
    }

    /** @test */
    public function test_update_validates_required_fields()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($this->user)
            ->put("/restaurants/{$restaurant->id}", ['name' => '']);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function test_destroy_deletes_restaurant()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/restaurants/{$restaurant->id}");

        $response->assertStatus(302)
            ->assertRedirect('/restaurants')
            ->assertSessionHas('success', 'Ristorante eliminato con successo');

        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]);
    }

    /** @test */
    public function test_destroy_handles_non_existent_restaurant()
    {
        $response = $this->actingAs($this->user)
            ->delete('/restaurants/999');

        $response->assertStatus(404);
    }
} 