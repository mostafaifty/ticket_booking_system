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

class RailwayTicketBookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $passenger;
    protected User $otherPassenger;
    protected User $admin;
    protected Train $train;
    protected Station $stationDhaka;
    protected Station $stationChittagong;
    protected TrainSchedule $schedule;
    protected Seat $seat1;
    protected Seat $seat2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passenger = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'phone' => '+8801700112233',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->otherPassenger = User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'phone' => '+8801800112233',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->train = Train::create([
            'train_number' => '701',
            'train_name' => 'Subarna Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 20,
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
            'seat_number' => '1',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->seat2 = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '2',
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

    public function test_passenger_can_successfully_book_ticket_and_redirect_to_confirmation(): void
    {
        $payload = [
            'seat_id' => $this->seat1->id,
            'passenger_name' => 'Alice Johnson',
            'passenger_phone' => '+8801700112233',
            'nid_or_passport' => '1995876543210',
            'age' => 29,
            'gender' => Passenger::GENDER_FEMALE,
        ];

        $response = $this->actingAs($this->passenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $booking = Booking::first();
        $this->assertNotNull($booking);

        $response->assertRedirect(route('bookings.confirmation', $booking));
        $response->assertSessionHas('success');

        // Check Booking table attributes
        $this->assertEquals($this->passenger->id, $booking->user_id);
        $this->assertEquals($this->schedule->id, $booking->train_schedule_id);
        $this->assertEquals($this->seat1->id, $booking->seat_id);
        $this->assertEquals(450.00, $booking->total_fare);
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);
        $this->assertStringStartsWith('BK-', $booking->booking_code);

        // Check Passenger table attributes
        $this->assertDatabaseHas('passengers', [
            'booking_id' => $booking->id,
            'name' => 'Alice Johnson',
            'phone' => '+8801700112233',
            'nid_or_passport' => '1995876543210',
            'age' => 29,
            'gender' => Passenger::GENDER_FEMALE,
        ]);
    }

    public function test_confirmation_voucher_renders_all_ticket_details_and_pnr(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-CONFIRM001',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'name' => 'Alice Johnson',
            'phone' => '+8801700112233',
            'nid_or_passport' => '1995876543210',
            'age' => 29,
            'gender' => Passenger::GENDER_FEMALE,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('bookings.confirmation', $booking));

        $response->assertStatus(200);
        $response->assertSee('BK-CONFIRM001');
        $response->assertSee('Subarna Express');
        $response->assertSee('Dhaka (Kamalapur)');
        $response->assertSee('Chittagong Railway Station');
        $response->assertSee('KA-1');
        $response->assertSee('SNIGDHA');
        $response->assertSee('Alice Johnson');
        $response->assertSee('450.00');
    }

    public function test_booking_calculates_fare_on_server_and_ignores_client_tampering(): void
    {
        $payload = [
            'seat_id' => $this->seat1->id,
            'passenger_name' => 'Alice Johnson',
            'passenger_phone' => '+8801700112233',
            'gender' => Passenger::GENDER_FEMALE,
            'fare' => 10.00, // Client tries to spoof fare
        ];

        $this->actingAs($this->passenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $booking = Booking::first();
        // Fare MUST be server's schedule fare (450.00), not client's 10.00
        $this->assertEquals(450.00, $booking->total_fare);
    }

    public function test_booking_fails_with_invalid_passenger_information(): void
    {
        $payload = [
            'seat_id' => $this->seat1->id,
            'passenger_name' => 'A', // Too short
            'passenger_phone' => '12', // Too short
            'gender' => 'invalid_gender',
        ];

        $response = $this->actingAs($this->passenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertSessionHasErrors(['passenger_name', 'passenger_phone', 'gender']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_duplicate_seat_booking_fails_and_rolls_back(): void
    {
        // First booking takes seat 1
        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-FIRSTBOOK',
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $payload = [
            'seat_id' => $this->seat1->id,
            'passenger_name' => 'Bob Smith',
            'passenger_phone' => '+8801800112233',
            'gender' => Passenger::GENDER_MALE,
        ];

        $response = $this->actingAs($this->otherPassenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('passengers', 0);
    }

    public function test_booking_fails_if_train_schedule_is_cancelled(): void
    {
        $this->schedule->update(['status' => TrainSchedule::STATUS_CANCELLED]);

        $payload = [
            'seat_id' => $this->seat1->id,
            'passenger_name' => 'Alice Johnson',
            'passenger_phone' => '+8801700112233',
            'gender' => Passenger::GENDER_FEMALE,
        ];

        $response = $this->actingAs($this->passenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_passenger_cannot_view_another_users_booking_confirmation(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ALICE001',
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Bob tries to access Alice's booking confirmation
        $response = $this->actingAs($this->otherPassenger)
            ->get(route('bookings.confirmation', $booking));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_booking_confirmation_and_all_bookings(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ALICE001',
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('bookings.confirmation', $booking));

        $response->assertStatus(200);
        $response->assertSee('BK-ALICE001');

        $adminIndexResponse = $this->actingAs($this->admin)->get(route('admin.bookings.index'));
        $adminIndexResponse->assertStatus(200);
        $adminIndexResponse->assertSee('BK-ALICE001');
    }

    public function test_passenger_can_view_their_booking_history(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ALICE001',
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passenger)
            ->get(route('passenger.bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('BK-ALICE001');
        $response->assertSee('Subarna Express');
    }
}
