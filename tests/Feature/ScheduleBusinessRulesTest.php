<?php

namespace Tests\Feature;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Train $train;
    protected Station $stationDhaka;
    protected Station $stationSylhet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->train = Train::factory()->create([
            'train_name' => 'Parabat Express',
            'status' => Train::STATUS_ACTIVE,
        ]);

        $this->stationDhaka = Station::factory()->create([
            'name' => 'Dhaka Kamalapur',
            'code' => 'DA',
        ]);

        $this->stationSylhet = Station::factory()->create([
            'name' => 'Sylhet Junction',
            'code' => 'SYL',
        ]);
    }

    /**
     * Test 1: Admin can create schedule with valid attributes.
     */
    public function test_schedule_creation_with_valid_attributes(): void
    {
        $payload = [
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationSylhet->id,
            'departure_time' => '06:20',
            'arrival_time' => '13:00',
            'journey_date' => now()->addDays(5)->toDateString(),
            'fare' => 380.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $payload);

        $response->assertRedirect(route('admin.schedules.index'));
        $this->assertDatabaseHas('train_schedules', [
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationSylhet->id,
            'fare' => 380.00,
        ]);
    }

    /**
     * Test 2: Schedule creation fails when departure and arrival stations are identical.
     */
    public function test_schedule_creation_fails_when_stations_are_identical(): void
    {
        $payload = [
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationDhaka->id, // Same station!
            'departure_time' => '06:20:00',
            'arrival_time' => '13:00:00',
            'journey_date' => now()->addDays(5)->toDateString(),
            'fare' => 380.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $payload);

        $response->assertSessionHasErrors('arrival_station_id');
        $this->assertDatabaseCount('train_schedules', 0);
    }

    /**
     * Test 3: Schedule search returns matching available schedules.
     */
    public function test_schedule_search_returns_matching_schedules(): void
    {
        $journeyDate = now()->addDays(4)->toDateString();

        TrainSchedule::factory()->create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationSylhet->id,
            'journey_date' => $journeyDate,
            'fare' => 380.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $response = $this->get(route('trains.search', [
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationSylhet->id,
            'journey_date' => $journeyDate,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Parabat Express');
        $response->assertSee('Dhaka Kamalapur');
        $response->assertSee('Sylhet Junction');
    }
}
