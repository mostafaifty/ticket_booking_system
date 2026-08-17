<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseEntityTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_station_and_retrieve_schedules(): void
    {
        $stationA = Station::factory()->create(['name' => 'Dhaka Station', 'code' => 'DHK']);
        $stationB = Station::factory()->create(['name' => 'Chittagong Station', 'code' => 'CTG']);
        $train = Train::factory()->create();

        $schedule = TrainSchedule::factory()->create([
            'train_id' => $train->id,
            'departure_station_id' => $stationA->id,
            'arrival_station_id' => $stationB->id,
        ]);

        $this->assertCount(1, $stationA->departureSchedules);
        $this->assertCount(1, $stationB->arrivalSchedules);
        $this->assertEquals($train->id, $schedule->train->id);
    }

    public function test_train_has_seats_and_schedules(): void
    {
        $train = Train::factory()->create();

        $seat = Seat::factory()->create([
            'train_id' => $train->id,
            'coach' => 'KA',
            'seat_number' => '1',
            'seat_class' => Seat::CLASS_AC_BERTH,
        ]);

        $this->assertCount(1, $train->seats);
        $this->assertEquals($train->id, $seat->train->id);
    }

    public function test_booking_and_passenger_relationship(): void
    {
        $user = User::factory()->create();
        $schedule = TrainSchedule::factory()->create();
        $seat = Seat::factory()->create(['train_id' => $schedule->train_id]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'train_schedule_id' => $schedule->id,
            'seat_id' => $seat->id,
            'booking_code' => 'BK-TEST999',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $passenger = Passenger::create([
            'booking_id' => $booking->id,
            'name' => 'Tanvir Hasan',
            'phone' => '+8801700112233',
            'nid_or_passport' => '1234567890',
            'age' => 25,
            'gender' => Passenger::GENDER_MALE,
        ]);

        $this->assertEquals($user->id, $booking->user->id);
        $this->assertEquals($seat->id, $booking->seat->id);
        $this->assertEquals($schedule->id, $booking->trainSchedule->id);
        $this->assertEquals($passenger->name, $booking->passenger->name);
        $this->assertEquals($booking->id, $passenger->booking->id);
    }

    public function test_prevents_duplicate_booking_of_same_seat_for_same_schedule(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $schedule = TrainSchedule::factory()->create();
        $seat = Seat::factory()->create(['train_id' => $schedule->train_id]);

        // First booking succeeds
        Booking::create([
            'user_id' => $userA->id,
            'train_schedule_id' => $schedule->id,
            'seat_id' => $seat->id,
            'booking_code' => 'BK-FIRST',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Check isSeatBooked helper recognizes the seat as booked
        $this->assertTrue($schedule->isSeatBooked($seat->id));

        // Submitting duplicate reservation via booking route is rejected
        $response = $this->actingAs($userB)->post(route('bookings.store', $schedule), [
            'seat_id' => $seat->id,
            'passenger_name' => 'Duplicate Passenger',
            'passenger_phone' => '+8801700998877',
            'gender' => Passenger::GENDER_MALE,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 1);
    }
}
