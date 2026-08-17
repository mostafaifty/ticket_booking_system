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

class TicketAndBookingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $passenger;
    protected User $otherPassenger;
    protected User $admin;
    protected Train $train;
    protected Station $stationDhaka;
    protected Station $stationChittagong;
    protected TrainSchedule $upcomingSchedule;
    protected TrainSchedule $pastSchedule;
    protected Seat $seat1;
    protected Seat $seat2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passenger = User::factory()->create([
            'name' => 'Alice Johnson',
            'phone' => '+8801700112233',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->otherPassenger = User::factory()->create([
            'name' => 'Bob Smith',
            'phone' => '+8801800112233',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin Controller',
            'role' => User::ROLE_ADMIN,
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
            'name' => 'Chittagong Railway Station',
            'code' => 'CTG',
            'location' => 'Station Road, Chittagong',
        ]);

        $this->seat1 = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '12',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->seat2 = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '14',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        // Upcoming schedule (Tomorrow)
        $this->upcomingSchedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        // Past schedule (3 days ago)
        $this->pastSchedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '15:00:00',
            'arrival_time' => '20:30:00',
            'journey_date' => now()->subDays(3)->toDateString(),
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_COMPLETED,
        ]);
    }

    public function test_passenger_can_view_current_upcoming_bookings(): void
    {
        $currentBooking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-UPCOMING01',
            'booking_date' => now(),
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.bookings.index', ['tab' => 'current']));

        $response->assertStatus(200);
        $response->assertSee('BK-UPCOMING01');
        $response->assertSee('Subarna Express');
        $response->assertSee('Current & Upcoming Journeys', false);
    }

    public function test_passenger_can_view_past_bookings_history(): void
    {
        $pastBooking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->pastSchedule->id,
            'seat_id' => $this->seat2->id,
            'booking_code' => 'BK-PAST0001',
            'booking_date' => now()->subDays(4),
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.bookings.index', ['tab' => 'past']));

        $response->assertStatus(200);
        $response->assertSee('BK-PAST0001');
        $response->assertSee('Past Trips History');
    }

    public function test_ticket_view_renders_all_required_passenger_and_journey_fields(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-FULLTICKET',
            'booking_date' => now(),
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'name' => 'Alice Johnson',
            'phone' => '+8801700112233',
            'nid_or_passport' => '1995123456789',
            'age' => 29,
            'gender' => Passenger::GENDER_FEMALE,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.bookings.ticket', $booking));

        $response->assertStatus(200);

        // Required ticket fields check
        $response->assertSee('BK-FULLTICKET');               // Booking ID / Code
        $response->assertSee('Alice Johnson');               // Passenger name
        $response->assertSee('+8801700112233');             // Phone
        $response->assertSee('Subarna Express');             // Train name
        $response->assertSee('701');                         // Train number
        $response->assertSee('Dhaka (Kamalapur)');           // Departure station
        $response->assertSee('Chittagong Railway Station');  // Arrival station
        $response->assertSee($this->upcomingSchedule->formatted_journey_date); // Journey date
        $response->assertSee('07:00 AM');                    // Departure time
        $response->assertSee('12:30 PM');                    // Arrival time
        $response->assertSee('12');                          // Seat number
        $response->assertSee('KA');                          // Coach
        $response->assertSee('420.00');                      // Fare
        $response->assertSee('confirmed');                   // Booking status
        $response->assertSee('Print Ticket');                // Print Ticket button
    }

    public function test_passenger_cannot_view_or_print_another_passengers_ticket(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ALICEPRIVACY',
            'booking_date' => now(),
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->otherPassenger)
            ->get(route('passenger.bookings.ticket', $booking));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_and_print_any_passengers_ticket(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ADMINACCESS',
            'booking_date' => now(),
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('passenger.bookings.ticket', $booking));

        $response->assertStatus(200);
        $response->assertSee('BK-ADMINACCESS');
    }

    public function test_passenger_can_search_booking_history_by_pnr(): void
    {
        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-SEARCHTARGET',
            'booking_date' => now(),
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.bookings.index', ['tab' => 'all', 'search' => 'SEARCHTARGET']));

        $response->assertStatus(200);
        $response->assertSee('BK-SEARCHTARGET');
    }
}
