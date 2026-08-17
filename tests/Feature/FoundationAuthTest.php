<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Railway Ticket Booking');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_registration_page_renders_successfully(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Create Account');
    }

    public function test_passenger_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+8801712345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('passenger.dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => User::ROLE_PASSENGER,
            'phone' => '+8801712345678',
        ]);
    }

    public function test_passenger_can_login_and_redirects_to_passenger_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PASSENGER,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('passenger.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_and_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'password' => bcrypt('adminpass123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'adminpass123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_passenger_cannot_access_admin_dashboard(): void
    {
        $passenger = User::factory()->create([
            'role' => User::ROLE_PASSENGER,
        ]);

        $response = $this->actingAs($passenger)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Administrative Overview');
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $responseAdmin = $this->get(route('admin.dashboard'));
        $responseAdmin->assertRedirect(route('login'));

        $responsePassenger = $this->get(route('passenger.dashboard'));
        $responsePassenger->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
