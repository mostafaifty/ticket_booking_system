<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatManagementAndSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $passenger;
    protected Train $train;
    protected Station $stationDhaka;
    protected Station $stationChittagong;
    protected TrainSchedule $schedule;
    protected Seat $seat1;
    protected Seat $seat2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'System Admin',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->passenger = User::factory()->create([
            'name' => 'John Doe',
            'phone' => '+8801711223344',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->train = Train::create([
            'train_number' => '701',
            'train_name' => 'Subarna Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 2,
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

        $this->seat1 = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '1',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->seat2 = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '2',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->schedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 420.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);
    }

    public function test_admin_can_view_train_seats_management_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.trains.seats.index', $this->train));

        $response->assertStatus(200);
        $response->assertSee('Subarna Express');
        $response->assertSee('Coach KA');
        $response->assertSee('SNIGDHA');
    }

    public function test_admin_can_add_a_single_seat_to_a_train(): void
    {
        $payload = [
            'coach' => 'KHA',
            'seat_number' => '1',
            'seat_class' => Seat::CLASS_SHOVON_CHAIR,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.trains.seats.store', $this->train), $payload);

        $response->assertRedirect(route('admin.trains.seats.index', $this->train));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('seats', [
            'train_id' => $this->train->id,
            'coach' => 'KHA',
            'seat_number' => '1',
            'seat_class' => Seat::CLASS_SHOVON_CHAIR,
        ]);

        // Total seats count should increment to 3
        $this->assertEquals(3, $this->train->fresh()->total_seats);
    }

    public function test_admin_can_bulk_generate_seats_for_a_coach(): void
    {
        $payload = [
            'coach' => 'GA',
            'seat_class' => Seat::CLASS_SHOVON_CHAIR,
            'seat_count' => 10,
            'start_number' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.trains.seats.generate', $this->train), $payload);

        $response->assertRedirect(route('admin.trains.seats.index', $this->train));
        $response->assertSessionHas('success');

        $this->assertEquals(10, $this->train->seats()->where('coach', 'GA')->count());
        $this->assertEquals(12, $this->train->fresh()->total_seats);
    }

    public function test_admin_can_delete_a_seat_without_bookings(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.seats.destroy', $this->seat2));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('seats', ['id' => $this->seat2->id]);
        $this->assertEquals(1, $this->train->fresh()->total_seats);
    }

    public function test_passenger_can_view_seat_selection_map_with_available_and_booked_seats(): void
    {
        // Mark seat 1 as booked
        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-SEAT0001',
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passenger)->get(route('schedules.seats', $this->schedule));

        $response->assertStatus(200);
        $response->assertSee('Subarna Express');
        $response->assertSee('Coach KA');
        $response->assertSee('BOOKED');
        $response->assertSee('1 Seats Left');
    }

    public function test_passenger_can_select_an_available_seat_and_complete_booking(): void
    {
        $payload = [
            'seat_id' => $this->seat2->id,
            'passenger_name' => 'John Doe',
            'passenger_phone' => '+8801711223344',
            'nid_or_passport' => '1990123456789',
            'age' => 32,
            'gender' => Passenger::GENDER_MALE,
        ];

        $response = $this->actingAs($this->passenger)
            ->post(route('schedules.seats.select', $this->schedule), $payload);

        $response->assertRedirect(route('passenger.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat2->id,
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $this->assertDatabaseHas('passengers', [
            'name' => 'John Doe',
            'phone' => '+8801711223344',
            'nid_or_passport' => '1990123456789',
            'age' => 32,
            'gender' => Passenger::GENDER_MALE,
        ]);
    }

    public function test_duplicate_booking_prevention_fails_when_seat_already_booked(): void
    {
        // First booking takes seat 1
        Booking::create([
            'user_id' => $this->passenger->id,
            'train_schedule_id' => $this->schedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-EXISTING',
            'total_fare' => 420.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $secondPassenger = User::factory()->create(['role' => User::ROLE_PASSENGER]);

        // Attempt second booking for the same seat on same schedule
        $payload = [
            'seat_id' => $this->seat1->id,
            'passenger_name' => 'Another Passenger',
            'passenger_phone' => '+8801799887766',
            'gender' => Passenger::GENDER_FEMALE,
        ];

        $response = $this->actingAs($secondPassenger)
            ->post(route('schedules.seats.select', $this->schedule), $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_guest_user_cannot_submit_seat_booking_without_authentication(): void
    {
        $payload = [
            'seat_id' => $this->seat2->id,
            'passenger_name' => 'Guest Passenger',
            'passenger_phone' => '+8801711000000',
            'gender' => Passenger::GENDER_MALE,
        ];

        $response = $this->post(route('schedules.seats.select', $this->schedule), $payload);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('bookings', 0);
    }
}
