<?php

namespace Database\Factories;

use App\Models\Seat;
use App\Models\Train;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeatFactory extends Factory
{
    protected $model = Seat::class;

    public function definition(): array
    {
        return [
            'train_id' => Train::factory(),
            'seat_number' => (string) fake()->numberBetween(1, 40),
            'coach' => fake()->randomElement(['KA', 'KHA', 'GA', 'GHA']),
            'seat_class' => Seat::CLASS_SHOVON_CHAIR,
        ];
    }
}
