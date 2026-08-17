<?php

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition(): array
    {
        $city = fake()->unique()->city();
        return [
            'name' => $city . ' Railway Station',
            'code' => strtoupper(fake()->unique()->bothify('S##?')),
            'location' => $city . ', Bangladesh',
        ];
    }
}
