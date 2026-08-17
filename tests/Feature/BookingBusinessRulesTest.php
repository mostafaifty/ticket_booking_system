<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Seat;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $passenger;
    protected User $otherPassenger;
    protected Train $train;
    protected TrainSchedule $schedule;
    protected Seat $seat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passenger = User::factory()->create(['role' => User::ROLE_PASSENGER]);
        $this->otherPassenger = User::factory()->create(['role' => User::ROLE_PASSENGER]);
        $this->train = Train::factory()->create();
        $this->seat = Seat::factory()->create(['train_id' => $this->train->id]);
        $this->schedule = TrainSchedule::factory()->create([
            'train_id' => $this->train->id,
            'fare' => 620.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);
    }

    /**
     * Test 1: Successful booking generates PNR, passenger record, and redirects to confirmation.
     */
    public function test_successful_booking(): void
    {
        $payload = [
            'seat_id' => $this->seat->id,
            'passenger_name' => 'Michael Scott',
            'passenger_phone' => '+8801755443322',
            'nid_or_passport' => '1234567890',
            'age' => 42,
            'gender' => 'male',
        ];

        $response = $this->actingAs($this->passenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $booking = Booking::where('user_id', $this->passenger->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);
        $this->assertEquals(620.00, $booking->total_fare);
        $this->assertNotNull($booking->booking_code);

        $response->assertRedirect(route('bookings.confirmation', $booking));
        $this->assertDatabaseHas('passengers', [
            'booking_id' => $booking->id,
            'name' => 'Michael Scott',
            'phone' => '+8801755443322',
        ]);
    }

    /**
     * Test 2: Booking fails when passenger data is invalid.
     */
    public function test_booking_fails_with_invalid_passenger_data(): void
    {
        $payload = [
            'seat_id' => $this->seat->id,
            'passenger_name' => '', // Empty name
            'passenger_phone' => '12', // Too short
            'gender' => 'invalid_gender',
        ];

        $response = $this->actingAs($this->passenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertSessionHasErrors(['passenger_name', 'passenger_phone', 'gender']);
        $this->assertDatabaseCount('bookings', 0);
    }

    /**
     * Test 3: Booking ownership verification prevents unauthorized passenger access.
     */
    public function test_booking_ownership_protection(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-OWNER-TEST',
            'booking_date' => now(),
            'total_fare' => 620.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Other passenger cannot access confirmation or ticket slip
        $this->actingAs($this->otherPassenger)
            ->get(route('bookings.confirmation', $booking))
            ->assertStatus(403);

        $this->actingAs($this->otherPassenger)
            ->get(route('passenger.bookings.ticket', $booking))
            ->assertStatus(403);
    }

    /**
     * Test 4: Fare is strictly calculated on the server.
     */
    public function test_fare_calculation_server_side(): void
    {
        $payload = [
            'seat_id' => $this->seat->id,
            'passenger_name' => 'Dwight Schrute',
            'passenger_phone' => '+8801811223344',
            'gender' => 'male',
            'total_fare' => 10.00, // Client attempt to manipulate price
            'fare' => 5.00,
        ];

        $this->actingAs($this->passenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertEquals(620.00, $booking->total_fare);
    }

    /**
     * Test 5: Transaction rollback occurs if an error happens during booking.
     */
    public function test_transaction_rollback_on_failure(): void
    {
        // Cancel the schedule so booking is rejected
        $this->schedule->update(['status' => TrainSchedule::STATUS_CANCELLED]);

        $payload = [
            'seat_id' => $this->seat->id,
            'passenger_name' => 'Pam Beesly',
            'passenger_phone' => '+8801911223344',
            'gender' => 'female',
        ];

        $response = $this->actingAs($this->passenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('passengers', 0);
    }
}
