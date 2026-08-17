<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $passenger;
    protected Train $train;
    protected Station $stationDhaka;
    protected Station $stationChittagong;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->passenger = User::factory()->create([
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->train = Train::create([
            'train_number' => '701',
            'train_name' => 'Subarna Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 40,
            'status' => Train::STATUS_ACTIVE,
        ]);

        $this->stationDhaka = Station::create([
            'name' => 'Dhaka (Kamalapur)',
            'code' => 'DA',
            'location' => 'Kamalapur, Dhaka',
        ]);

        $this->stationChittagong = Station::create([
            'name' => 'Chittagong Railway Station',
            'code' => 'CTG',
            'location' => 'Station Road, Chittagong',
        ]);
    }

    public function test_admin_can_view_schedules_index_with_pagination(): void
    {
        TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.schedules.index'));

        $response->assertStatus(200);
        $response->assertSee('Subarna Express');
        $response->assertSee('Dhaka (Kamalapur)');
        $response->assertSee('Chittagong Railway Station');
        $response->assertSee('420.00');
    }

    public function test_admin_can_view_create_schedule_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.schedules.create'));

        $response->assertStatus(200);
        $response->assertSee('Subarna Express');
        $response->assertSee('Dhaka (Kamalapur)');
    }

    public function test_admin_can_create_schedule_successfully(): void
    {
        $payload = [
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '08:30',
            'arrival_time' => '14:00',
            'journey_date' => now()->addDays(2)->toDateString(),
            'fare' => 450.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.schedules.store'), $payload);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('train_schedules', [
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'fare' => 450.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);
    }

    public function test_schedule_creation_fails_when_departure_and_arrival_station_are_identical(): void
    {
        $payload = [
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationDhaka->id, // SAME STATION
            'departure_time' => '08:30',
            'arrival_time' => '14:00',
            'journey_date' => now()->addDays(2)->toDateString(),
            'fare' => 450.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.schedules.store'), $payload);

        $response->assertSessionHasErrors(['arrival_station_id']);
        $this->assertDatabaseCount('train_schedules', 0);
    }

    public function test_schedule_creation_fails_with_invalid_fare_or_past_date(): void
    {
        $payload = [
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '08:30',
            'arrival_time' => '14:00',
            'journey_date' => now()->subDay()->toDateString(), // PAST DATE
            'fare' => -50.00, // NEGATIVE FARE
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.schedules.store'), $payload);

        $response->assertSessionHasErrors(['journey_date', 'fare', 'status']);
        $this->assertDatabaseCount('train_schedules', 0);
    }

    public function test_admin_can_view_schedule_details(): void
    {
        $schedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.schedules.show', $schedule));

        $response->assertStatus(200);
        $response->assertSee('Trip Information');
        $response->assertSee('Subarna Express');
        $response->assertSee('Dhaka (Kamalapur)');
        $response->assertSee('Chittagong Railway Station');
    }

    public function test_admin_can_update_schedule_and_status(): void
    {
        $schedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $updatePayload = [
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:30',
            'arrival_time' => '13:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 480.00,
            'status' => TrainSchedule::STATUS_DELAYED,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.schedules.update', $schedule), $updatePayload);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('train_schedules', [
            'id' => $schedule->id,
            'fare' => 480.00,
            'status' => TrainSchedule::STATUS_DELAYED,
        ]);
    }

    public function test_admin_can_delete_schedule_without_bookings(): void
    {
        $schedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.schedules.destroy', $schedule));

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('train_schedules', ['id' => $schedule->id]);
    }

    public function test_admin_cannot_delete_schedule_with_confirmed_bookings(): void
    {
        $seat = Seat::create([
            'train_id' => $this->train->id,
            'seat_number' => '1',
            'coach' => 'KA',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $schedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $schedule->id,
            'seat_id' => $seat->id,
            'booking_code' => 'BK-TEST12345',
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.schedules.destroy', $schedule));

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('train_schedules', ['id' => $schedule->id]);
    }

    public function test_passenger_cannot_access_admin_schedule_management(): void
    {
        $response = $this->actingAs($this->passenger)->get(route('admin.schedules.index'));
        $response->assertStatus(403);
    }
}
