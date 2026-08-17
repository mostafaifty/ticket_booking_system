<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Seat;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $passenger;
    protected Train $train;
    protected TrainSchedule $schedule;
    protected Seat $seatAvailable;
    protected Seat $seatBooked;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passenger = User::factory()->create([
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->train = Train::factory()->create();

        $this->seatAvailable = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '10',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->seatBooked = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '11',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->schedule = TrainSchedule::factory()->create([
            'train_id' => $this->train->id,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        // Pre-book seat 11
        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seatBooked->id,
            'booking_code' => 'BK-SEAT-OCCUPIED',
            'booking_date' => now(),
            'total_fare' => 500.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Test 1: Available seat is displayed as selectable, and booked seat is marked occupied.
     */
    public function test_available_and_booked_seat_visualizer(): void
    {
        $response = $this->actingAs($this->passenger)
            ->get(route('schedules.seats', $this->schedule));

        $response->assertStatus(200);
        $response->assertSee('KA-10');
        $response->assertSee('KA-11');
    }

    /**
     * Test 2: Duplicate seat booking prevention stops booking of an already reserved seat.
     */
    public function test_duplicate_seat_booking_prevention(): void
    {
        $otherPassenger = User::factory()->create(['role' => User::ROLE_PASSENGER]);

        $payload = [
            'seat_id' => $this->seatBooked->id, // Already booked!
            'passenger_name' => 'Bob Second',
            'passenger_phone' => '+8801999887766',
            'gender' => 'male',
        ];

        $response = $this->actingAs($otherPassenger)
            ->post(route('bookings.store', $this->schedule), $payload);

        $response->assertSessionHas('error');
        // Only 1 booking should exist in database for this seat
        $this->assertEquals(1, Booking::where('seat_id', $this->seatBooked->id)->count());
    }
}
