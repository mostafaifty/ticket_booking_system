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

class PassengerTrainSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $passenger;
    protected Train $trainSubarna;
    protected Train $trainParabat;
    protected Station $stationDhaka;
    protected Station $stationChittagong;
    protected Station $stationSylhet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passenger = User::factory()->create([
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->trainSubarna = Train::create([
            'train_number' => '701',
            'train_name' => 'Subarna Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 50,
            'status' => Train::STATUS_ACTIVE,
        ]);

        $this->trainParabat = Train::create([
            'train_number' => '709',
            'train_name' => 'Parabat Express',
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

        $this->stationSylhet = Station::create([
            'name' => 'Sylhet Railway Station',
            'code' => 'SYL',
            'location' => 'Kadamtoli, Sylhet',
        ]);
    }

    public function test_train_search_page_renders_successfully(): void
    {
        $response = $this->get(route('trains.search'));

        $response->assertStatus(200);
        $response->assertSee('Search Available Train Routes');
        $response->assertSee('From (Departure Station)');
        $response->assertSee('To (Arrival Station)');
    }

    public function test_passenger_can_search_trains_by_route_and_date(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        // Schedule Dhaka -> Chittagong
        $schedule = TrainSchedule::create([
            'train_id' => $this->trainSubarna->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => $tomorrow,
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        // Schedule Dhaka -> Sylhet (Should not appear in Dhaka->Chittagong search)
        TrainSchedule::create([
            'train_id' => $this->trainParabat->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationSylhet->id,
            'departure_time' => '06:20:00',
            'arrival_time' => '13:00:00',
            'journey_date' => $tomorrow,
            'fare' => 380.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $queryParams = [
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'journey_date' => $tomorrow,
        ];

        $response = $this->actingAs($this->passenger)->get(route('trains.search', $queryParams));

        $response->assertStatus(200);
        $response->assertSee('Subarna Express');
        $response->assertSee('701');
        $response->assertSee('07:00 AM');
        $response->assertSee('12:30 PM');
        $response->assertSee('420.00');
        $response->assertSee('50 Seats Available');
        $response->assertDontSee('Parabat Express');
    }

    public function test_search_reflects_available_seat_count_after_bookings(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $schedule = TrainSchedule::create([
            'train_id' => $this->trainSubarna->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => $tomorrow,
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        $seat = Seat::create([
            'train_id' => $this->trainSubarna->id,
            'seat_number' => '1',
            'coach' => 'KA',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $schedule->id,
            'seat_id' => $seat->id,
            'booking_code' => 'BK-TESTBOOK01',
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $queryParams = [
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'journey_date' => $tomorrow,
        ];

        $response = $this->get(route('trains.search', $queryParams));

        $response->assertStatus(200);
        // Total 50 seats, 1 booked -> 49 available
        $response->assertSee('49 Seats Available');
    }

    public function test_search_validation_fails_when_departure_and_arrival_station_are_identical(): void
    {
        $queryParams = [
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationDhaka->id,
        ];

        $response = $this->get(route('trains.search', $queryParams));

        $response->assertSessionHasErrors(['arrival_station_id']);
    }

    public function test_search_displays_friendly_message_when_no_trains_match(): void
    {
        $queryParams = [
            'departure_station_id' => $this->stationChittagong->id,
            'arrival_station_id' => $this->stationSylhet->id,
            'journey_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->get(route('trains.search', $queryParams));

        $response->assertStatus(200);
        $response->assertSee('No Matching Trains Found');
    }

    public function test_search_excludes_cancelled_schedules(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        TrainSchedule::create([
            'train_id' => $this->trainSubarna->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => $tomorrow,
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_CANCELLED,
        ]);

        $queryParams = [
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'journey_date' => $tomorrow,
        ];

        $response = $this->get(route('trains.search', $queryParams));

        $response->assertStatus(200);
        $response->assertDontSee('Subarna Express');
    }
}
