<?php

namespace Tests\Feature;

use App\Models\Train;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $passenger;
    protected Train $train;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->passenger = User::factory()->create([
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->train = Train::factory()->create();
    }

    /**
     * Test 1: Admin can access all administrative control panel routes.
     */
    public function test_admin_can_access_all_admin_routes(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.schedules.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.schedules.create'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.trains.seats.index', $this->train))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.bookings.index'))->assertStatus(200);
    }

    /**
     * Test 2: Passenger cannot access any admin control panel routes (403 Forbidden).
     */
    public function test_passenger_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->passenger)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($this->passenger)->get(route('admin.schedules.index'))->assertStatus(403);
        $this->actingAs($this->passenger)->get(route('admin.schedules.create'))->assertStatus(403);
        $this->actingAs($this->passenger)->get(route('admin.trains.seats.index', $this->train))->assertStatus(403);
        $this->actingAs($this->passenger)->get(route('admin.bookings.index'))->assertStatus(403);
    }

    /**
     * Test 3: Unauthenticated guest accessing admin route is redirected to login.
     */
    public function test_guest_is_redirected_to_login_from_admin_routes(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.schedules.index'))->assertRedirect(route('login'));
    }
}
