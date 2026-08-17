<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $passenger;
    protected Train $train;
    protected Station $stationDhaka;
    protected Station $stationChittagong;
    protected TrainSchedule $schedule;
    protected Seat $seat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Controller',
            'email' => 'admin@railway.gov.bd',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->passenger = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->train = Train::create([
            'train_number' => '701',
            'train_name' => 'Subarna Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 50,
            'status' => Train::STATUS_ACTIVE,
        ]);

        $this->stationDhaka = Station::create([
            'name' => 'Dhaka (Kamalapur)',
            'code' => 'DA',
            'location' => 'Kamalapur, Dhaka',
        ]);

        $this->stationChittagong = Station::create([
            'name' => 'Chittagong Station',
            'code' => 'CTG',
            'location' => 'Station Road, Chittagong',
        ]);

        $this->seat = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '1',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->schedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 450.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Administrative Overview');
    }

    public function test_passenger_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->passenger)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_dashboard_displays_all_seven_metrics(): void
    {
        // Create confirmed booking
        $bookingConfirmed = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-CONFIRMED01',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Create cancelled booking
        $bookingCancelled = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-CANCELLED01',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Verify Metric Labels
        $response->assertSee('Total Passengers');
        $response->assertSee('Total Trains');
        $response->assertSee('Total Stations');
        $response->assertSee('Total Schedules');
        $response->assertSee('Total Bookings');
        $response->assertSee('Confirmed Bookings');
        $response->assertSee('Cancelled Bookings');

        // Pass stats to view
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_passengers'] === 1
                && $stats['total_trains'] === 1
                && $stats['total_stations'] === 2
                && $stats['total_schedules'] === 1
                && $stats['total_bookings'] === 2
                && $stats['confirmed_bookings'] === 1
                && $stats['cancelled_bookings'] === 1;
        });
    }

    public function test_admin_dashboard_displays_recent_bookings_table(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-RECENT999',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'name' => 'Alice Johnson',
            'phone' => '+8801700112233',
            'gender' => Passenger::GENDER_FEMALE,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recent Passenger Bookings');
        $response->assertSee('BK-RECENT999');
        $response->assertSee('Alice Johnson');
        $response->assertSee('Subarna Express');
        $response->assertSee('Dhaka (Kamalapur)');
        $response->assertSee('KA');
    }

    public function test_admin_dashboard_displays_all_quick_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manage Trains');
        $response->assertSee('Manage Stations');
        $response->assertSee('Manage Schedules');
        $response->assertSee('Manage Seats');
        $response->assertSee('View Bookings');
        $response->assertSee('Manage Users');
    }
}
