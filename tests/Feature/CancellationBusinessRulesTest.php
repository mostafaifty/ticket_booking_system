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

class CancellationBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $passenger;
    protected Train $train;
    protected TrainSchedule $schedule;
    protected Seat $seat;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passenger = User::factory()->create(['role' => User::ROLE_PASSENGER]);
        $this->train = Train::factory()->create();
        $this->seat = Seat::factory()->create(['train_id' => $this->train->id]);
        $this->schedule = TrainSchedule::factory()->create([
            'train_id' => $this->train->id,
            'journey_date' => now()->addDays(3)->toDateString(),
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat->id,
            'booking_code' => 'BK-CANCEL-TEST',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Test 1: Successful cancellation updates booking status to cancelled and preserves history.
     */
    public function test_successful_cancellation_updates_status_and_preserves_history(): void
    {
        $response = $this->actingAs($this->passenger)
            ->post(route('passenger.bookings.cancel', $this->booking));

        $this->booking->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $this->booking->status);
        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => Booking::STATUS_CANCELLED,
        ]);
    }

    /**
     * Test 2: Duplicate cancellation attempt is rejected.
     */
    public function test_duplicate_cancellation_prevention(): void
    {
        // Cancel first time
        $this->actingAs($this->passenger)
            ->post(route('passenger.bookings.cancel', $this->booking));

        // Attempt cancel second time
        $response = $this->actingAs($this->passenger)
            ->post(route('passenger.bookings.cancel', $this->booking));

        $response->assertSessionHas('error');
    }

    /**
     * Test 3: Seat is released after cancellation and can be booked by another passenger.
     */
    public function test_seat_is_released_and_can_be_booked_again(): void
    {
        // Cancel booking
        $this->actingAs($this->passenger)
            ->post(route('passenger.bookings.cancel', $this->booking));

        // Another passenger books the same seat
        $newPassenger = User::factory()->create(['role' => User::ROLE_PASSENGER]);
        $payload = [
            'seat_id' => $this->seat->id,
            'passenger_name' => 'Jim Halpert',
            'passenger_phone' => '+8801722334455',
            'gender' => 'male',
        ];

        $response = $this->actingAs($newPassenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertRedirect();
        $newBooking = Booking::where('user_id', $newPassenger->id)->first();
        $this->assertNotNull($newBooking);
        $this->assertEquals(Booking::STATUS_CONFIRMED, $newBooking->status);
        $this->assertEquals($this->seat->id, $newBooking->seat_id);
    }
}
