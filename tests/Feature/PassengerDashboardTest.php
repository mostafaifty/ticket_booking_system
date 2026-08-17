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

class PassengerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $passenger;
    protected Train $train;
    protected Station $stationDhaka;
    protected Station $stationChittagong;
    protected TrainSchedule $upcomingSchedule;
    protected Seat $seat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passenger = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'phone' => '+8801700112233',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->train = Train::create([
            'train_number' => '701',
            'train_name' => 'Subarna Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 40,
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
            'seat_number' => '5',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->upcomingSchedule = TrainSchedule::create([
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

    public function test_passenger_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Alice Johnson!');
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get(route('passenger.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_displays_all_booking_metric_counters(): void
    {
        // Confirmed active booking
        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-PASS-ACTIVE',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Cancelled booking
        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-PASS-CANCEL',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Bookings');
        $response->assertSee('Active Bookings');
        $response->assertSee('Cancelled Bookings');

        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_bookings'] === 2
                && $stats['active_bookings'] === 1
                && $stats['cancelled_bookings'] === 1;
        });
    }

    public function test_dashboard_displays_all_quick_actions(): void
    {
        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Search Train');
        $response->assertSee('My Bookings');
        $response->assertSee('My Profile');
    }

    public function test_dashboard_displays_upcoming_journey_card(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-UPCOMING-SHOW',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Next Upcoming Journey');
        $response->assertSee('Subarna Express');
        $response->assertSee('KA-5');
        $response->assertSee('BK-UPCOMING-SHOW');
        $response->assertSee('Dhaka (Kamalapur)');
        $response->assertSee('Chittagong Station');
    }

    public function test_dashboard_displays_recent_bookings_table(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-RECENT-TABLE',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recent Bookings');
        $response->assertSee('BK-RECENT-TABLE');
        $response->assertSee('Subarna Express');
    }
}
