<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainScheduleFactory extends Factory
{
    protected $model = TrainSchedule::class;

    public function definition(): array
    {
        return [
            'train_id' => Train::factory(),
            'departure_station_id' => Station::factory(),
            'arrival_station_id' => Station::factory(),
            'departure_time' => '07:00:00',
            'arrival_time' => '13:00:00',
            'journey_date' => now()->addDays(2)->format('Y-m-d'),
            'fare' => 450.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ];
    }
}
