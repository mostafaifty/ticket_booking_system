<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

class PassengerFactory extends Factory
{
    protected $model = Passenger::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'name' => fake()->name(),
            'phone' => '+88017' . fake()->numberBetween(10000000, 99999999),
            'nid_or_passport' => (string) fake()->numberBetween(1000000000, 9999999999),
            'age' => fake()->numberBetween(18, 65),
            'gender' => fake()->randomElement(['male', 'female']),
        ];
    }
}
