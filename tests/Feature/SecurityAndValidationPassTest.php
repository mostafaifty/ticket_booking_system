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

class SecurityAndValidationPassTest extends TestCase
{
    use RefreshDatabase;

    protected User $passengerA;
    protected User $passengerB;
    protected User $admin;
    protected Train $train;
    protected Station $stationA;
    protected Station $stationB;
    protected TrainSchedule $schedule;
    protected Seat $seatA;
    protected Seat $seatB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passengerA = User::factory()->create([
            'name' => 'Alice Passenger',
            'email' => 'alice@test.com',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->passengerB = User::factory()->create([
            'name' => 'Bob Attacker',
            'email' => 'bob@test.com',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin Controller',
            'email' => 'admin@railway.gov.bd',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->train = Train::create([
            'train_number' => '701',
            'train_name' => 'Security Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 40,
            'status' => Train::STATUS_ACTIVE,
        ]);

        $this->stationA = Station::create([
            'name' => 'Dhaka Kamalapur',
            'code' => 'DA',
            'location' => 'Dhaka',
        ]);

        $this->stationB = Station::create([
            'name' => 'Chittagong Station',
            'code' => 'CTG',
            'location' => 'Chittagong',
        ]);

        $this->seatA = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '1',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->seatB = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '2',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->schedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationA->id,
            'arrival_station_id' => $this->stationB->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDays(2)->toDateString(),
            'fare' => 500.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);
    }

    /**
     * 1. Authorization: Admin routes MUST NOT be accessible by passengers.
     */
    public function test_passenger_cannot_access_any_admin_routes(): void
    {
        // Admin Dashboard
        $this->actingAs($this->passengerA)->get(route('admin.dashboard'))->assertStatus(403);

        // Admin Schedules Index & Create
        $this->actingAs($this->passengerA)->get(route('admin.schedules.index'))->assertStatus(403);
        $this->actingAs($this->passengerA)->get(route('admin.schedules.create'))->assertStatus(403);

        // Admin Seats Management
        $this->actingAs($this->passengerA)->get(route('admin.trains.seats.index', $this->train))->assertStatus(403);

        // Admin All Bookings
        $this->actingAs($this->passengerA)->get(route('admin.bookings.index'))->assertStatus(403);
    }

    /**
     * 2. IDOR / Authorization: Passenger B cannot view Passenger A's booking or ticket.
     */
    public function test_passenger_cannot_view_another_passengers_booking_or_ticket(): void
    {
        $bookingAlice = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seatA->id,
            'booking_code' => 'BK-ALICE-SEC',
            'booking_date' => now(),
            'total_fare' => 500.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Bob attempts to view Alice's booking confirmation
        $this->actingAs($this->passengerB)
            ->get(route('bookings.confirmation', $bookingAlice))
            ->assertStatus(403);

        // Bob attempts to view Alice's ticket details
        $this->actingAs($this->passengerB)
            ->get(route('passenger.bookings.ticket', $bookingAlice))
            ->assertStatus(403);

        // Bob attempts to view Alice's show route
        $this->actingAs($this->passengerB)
            ->get(route('passenger.bookings.show', $bookingAlice))
            ->assertStatus(403);
    }

    /**
     * 3. IDOR / Authorization: Passenger B cannot cancel Passenger A's booking.
     */
    public function test_passenger_cannot_cancel_another_passengers_booking(): void
    {
        $bookingAlice = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seatA->id,
            'booking_code' => 'BK-ALICE-CANCEL',
            'booking_date' => now(),
            'total_fare' => 500.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Bob attempts to cancel Alice's booking
        $this->actingAs($this->passengerB)
            ->post(route('passenger.bookings.cancel', $bookingAlice))
            ->assertStatus(403);

        $bookingAlice->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $bookingAlice->status);
    }

    /**
     * 4. Privilege Escalation: Registration cannot set admin role.
     */
    public function test_registration_prevents_privilege_escalation_to_admin(): void
    {
        $payload = [
            'name' => 'Attacker',
            'email' => 'attacker@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin', // Tampered input
        ];

        $this->post(route('register'), $payload);

        $user = User::where('email', 'attacker@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(User::ROLE_PASSENGER, $user->role);
        $this->assertFalse($user->isAdmin());
    }

    /**
     * 5. Data Integrity: Booking fare is calculated strictly on the server.
     */
    public function test_booking_fare_is_server_calculated_and_immune_to_client_tampering(): void
    {
        $payload = [
            'seat_id' => $this->seatA->id,
            'passenger_name' => 'Alice Johnson',
            'passenger_phone' => '+8801700112233',
            'gender' => Passenger::GENDER_FEMALE,
            'total_fare' => 1.00, // Client tries to spoof fare
            'fare' => 0.00,
        ];

        $this->actingAs($this->passengerA)
            ->post(route('bookings.store', $this->schedule), $payload);

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertEquals(500.00, $booking->total_fare);
    }

    /**
     * 6. Validation: Invalid passenger data is rejected.
     */
    public function test_booking_rejects_invalid_passenger_input(): void
    {
        $payload = [
            'seat_id' => 999999, // Non-existent seat
            'passenger_name' => '', // Empty name
            'passenger_phone' => '123', // Too short
            'gender' => 'invalid_gender',
        ];

        $response = $this->actingAs($this->passengerA)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertSessionHasErrors(['seat_id', 'passenger_name', 'passenger_phone', 'gender']);
        $this->assertDatabaseCount('bookings', 0);
    }

    /**
     * 7. Concurrency & Integrity: Seat assigned to another train cannot be booked.
     */
    public function test_cannot_book_seat_belonging_to_a_different_train(): void
    {
        $otherTrain = Train::create([
            'train_number' => '702',
            'train_name' => 'Other Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 20,
            'status' => Train::STATUS_ACTIVE,
        ]);

        $alienSeat = Seat::create([
            'train_id' => $otherTrain->id,
            'coach' => 'GA',
            'seat_number' => '99',
            'seat_class' => Seat::CLASS_SHOVON_CHAIR,
        ]);

        $payload = [
            'seat_id' => $alienSeat->id,
            'passenger_name' => 'Alice Johnson',
            'passenger_phone' => '+8801700112233',
            'gender' => Passenger::GENDER_FEMALE,
        ];

        $response = $this->actingAs($this->passengerA)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 0);
    }
}
