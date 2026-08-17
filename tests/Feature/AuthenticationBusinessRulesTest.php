<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Passenger registration with valid input.
     */
    public function test_passenger_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
            'phone' => '+8801711223344',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('passenger.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'sarah@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Sarah Connor', $user->name);
        $this->assertEquals(User::ROLE_PASSENGER, $user->role);
    }

    /**
     * Test 2: Registration fails with invalid email or mismatched password.
     */
    public function test_registration_fails_with_invalid_email_or_password_mismatch(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Bad User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'mismatched',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * Test 3: Passenger login with valid credentials redirects to passenger dashboard.
     */
    public function test_passenger_login_redirects_to_passenger_dashboard(): void
    {
        $passenger = User::factory()->create([
            'email' => 'passenger@example.com',
            'password' => bcrypt('Password123!'),
            'role' => User::ROLE_PASSENGER,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'passenger@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('passenger.dashboard'));
        $this->assertAuthenticatedAs($passenger);
    }

    /**
     * Test 4: Admin login with valid credentials redirects to admin dashboard.
     */
    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('AdminPass123!'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'AdminPass123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test 5: Login fails with invalid password.
     */
    public function test_login_fails_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('CorrectPassword123!'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test 6: Authenticated user can logout.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
