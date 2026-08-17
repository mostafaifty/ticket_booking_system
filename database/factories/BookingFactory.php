<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Seat;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'train_schedule_id' => TrainSchedule::factory(),
            'seat_id' => Seat::factory(),
            'booking_code' => 'BK-' . strtoupper(Str::random(10)),
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ];
    }
}
