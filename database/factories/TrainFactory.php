<?php

namespace Database\Factories;

use App\Models\Train;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainFactory extends Factory
{
    protected $model = Train::class;

    public function definition(): array
    {
        return [
            'train_number' => (string) fake()->unique()->numberBetween(700, 799),
            'train_name' => fake()->randomElement([
                'Subarna Express',
                'Sonar Bangla Express',
                'Parabat Express',
                'Mohanagar Provati',
                'Silk City Express',
                'Kalni Express',
                'Turna Nishitha',
                'Ekota Express',
            ]),
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 40,
            'status' => Train::STATUS_ACTIVE,
        ];
    }
}
