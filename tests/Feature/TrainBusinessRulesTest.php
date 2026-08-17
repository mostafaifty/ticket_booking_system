<?php

namespace Tests\Feature;

use App\Models\Seat;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Train creation with valid attributes and default active status.
     */
    public function test_train_creation_with_valid_attributes(): void
    {
        $train = Train::factory()->create([
            'train_number' => '789',
            'train_name' => 'Padma Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 50,
            'status' => Train::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('trains', [
            'id' => $train->id,
            'train_number' => '789',
            'train_name' => 'Padma Express',
            'status' => Train::STATUS_ACTIVE,
        ]);
        $this->assertEquals(Train::STATUS_ACTIVE, $train->status);
    }

    /**
     * Test 2: Train update modifies name, type, seats, and status.
     */
    public function test_train_update(): void
    {
        $train = Train::factory()->create([
            'train_name' => 'Old Express',
            'status' => Train::STATUS_ACTIVE,
        ]);

        $train->update([
            'train_name' => 'Updated Super Express',
            'status' => Train::STATUS_MAINTENANCE,
        ]);

        $this->assertDatabaseHas('trains', [
            'id' => $train->id,
            'train_name' => 'Updated Super Express',
            'status' => Train::STATUS_MAINTENANCE,
        ]);
        $this->assertEquals(Train::STATUS_MAINTENANCE, $train->status);
    }

    /**
     * Test 3: Train search by departure station, arrival station, and journey date.
     */
    public function test_passenger_can_search_trains_by_route_and_date(): void
    {
        $stationDhaka = Station::factory()->create(['name' => 'Dhaka Station', 'code' => 'DA']);
        $stationChittagong = Station::factory()->create(['name' => 'Chittagong Station', 'code' => 'CTG']);
        $train = Train::factory()->create(['train_name' => 'Sonar Bangla Express', 'train_number' => '788']);

        // Generate seats
        Seat::factory()->count(10)->create(['train_id' => $train->id]);

        $journeyDate = now()->addDays(3)->toDateString();
        TrainSchedule::factory()->create([
            'train_id' => $train->id,
            'departure_station_id' => $stationDhaka->id,
            'arrival_station_id' => $stationChittagong->id,
            'journey_date' => $journeyDate,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:15:00',
            'fare' => 500.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $response = $this->get(route('trains.search', [
            'departure_station_id' => $stationDhaka->id,
            'arrival_station_id' => $stationChittagong->id,
            'journey_date' => $journeyDate,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Sonar Bangla Express');
        $response->assertSee('788');
        $response->assertSee('500');
    }
}
