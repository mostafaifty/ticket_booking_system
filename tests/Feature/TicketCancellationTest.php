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

class TicketCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $passengerA;
    protected User $passengerB;
    protected User $admin;
    protected Train $train;
    protected Station $stationDhaka;
    protected Station $stationChittagong;
    protected TrainSchedule $upcomingSchedule;
    protected TrainSchedule $pastSchedule;
    protected Seat $seat1;
    protected Seat $seat2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passengerA = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'phone' => '+8801700112233',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->passengerB = User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'phone' => '+8801800112233',
            'role' => User::ROLE_PASSENGER,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin Controller',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->train = Train::create([
            'train_number' => '701',
            'train_name' => 'Subarna Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 20,
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
            'seat_number' => '10',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $this->seat2 = Seat::create([
            'train_id' => $this->train->id,
            'coach' => 'KA',
            'seat_number' => '12',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        // Future schedule (tomorrow)
        $this->upcomingSchedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->addDay()->toDateString(),
            'fare' => 450.00,
            'status' => TrainSchedule::STATUS_SCHEDULED,
        ]);

        // Past schedule (2 days ago)
        $this->pastSchedule = TrainSchedule::create([
            'train_id' => $this->train->id,
            'departure_station_id' => $this->stationDhaka->id,
            'arrival_station_id' => $this->stationChittagong->id,
            'departure_time' => '07:00:00',
            'arrival_time' => '12:30:00',
            'journey_date' => now()->subDays(2)->toDateString(),
            'fare' => 450.00,
            'status' => TrainSchedule::STATUS_COMPLETED,
        ]);
    }

    public function test_passenger_can_successfully_cancel_an_eligible_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-CANCEL001',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'name' => 'Alice Johnson',
            'phone' => '+8801700112233',
            'gender' => Passenger::GENDER_FEMALE,
        ]);

        $response = $this->actingAs($this->passengerA)
            ->post(route('passenger.bookings.cancel', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Record must be preserved in DB with status cancelled
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->status);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_code' => 'BK-CANCEL001',
            'status' => Booking::STATUS_CANCELLED,
        ]);
    }

    public function test_seat_becomes_available_after_cancellation_and_can_be_booked_again(): void
    {
        // 1. Passenger A books seat 1
        $bookingA = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ALICEBOOK',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Verify Seat 1 is occupied
        $this->assertTrue($this->upcomingSchedule->isSeatBooked($this->seat1->id));

        // 2. Passenger A cancels booking
        $this->actingAs($this->passengerA)
            ->post(route('passenger.bookings.cancel', $bookingA));

        $bookingA->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $bookingA->status);

        // Verify Seat 1 is now available
        $this->assertFalse($this->upcomingSchedule->isSeatBooked($this->seat1->id));

        // 3. Passenger B can now successfully book Seat 1 on the same schedule
        $payloadB = [
            'seat_id' => $this->seat1->id,
            'passenger_name' => 'Bob Smith',
            'passenger_phone' => '+8801800112233',
            'gender' => Passenger::GENDER_MALE,
        ];

        $responseB = $this->actingAs($this->passengerB)
            ->post(route('bookings.store', $this->upcomingSchedule), $payloadB);

        $responseB->assertRedirect();
        $responseB->assertSessionHas('success');

        // Verify both booking records exist in DB (history preserved)
        $this->assertDatabaseCount('bookings', 2);
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingA->id,
            'user_id' => $this->passengerA->id,
            'status' => Booking::STATUS_CANCELLED,
        ]);
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->passengerB->id,
            'seat_id' => $this->seat1->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
    }

    public function test_duplicate_cancellation_attempt_fails(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ALREADYCAN',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($this->passengerA)
            ->post(route('passenger.bookings.cancel', $booking));

        $response->assertSessionHas('error');
    }

    public function test_cancellation_fails_for_past_or_completed_schedule(): void
    {
        $pastBooking = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->pastSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-PASTTRIP',
            'booking_date' => now()->subDays(3),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passengerA)
            ->post(route('passenger.bookings.cancel', $pastBooking));

        $response->assertSessionHas('error');
        $pastBooking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $pastBooking->status);
    }

    public function test_unauthorized_passenger_cannot_cancel_another_passengers_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ALICEOWNED',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Passenger B tries to cancel Alice's booking
        $response = $this->actingAs($this->passengerB)
            ->post(route('passenger.bookings.cancel', $booking));

        $response->assertStatus(403);
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);
    }

    public function test_admin_can_cancel_any_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-ADMINCAN',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('passenger.bookings.cancel', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $booking->refresh();
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->status);
    }

    public function test_cancel_button_is_only_visible_for_cancellable_bookings(): void
    {
        $cancellableBooking = Booking::create([
            'user_id' => $this->passengerA->id,
            'train_schedule_id' => $this->upcomingSchedule->id,
            'seat_id' => $this->seat1->id,
            'booking_code' => 'BK-CANCELLABLE',
            'booking_date' => now(),
            'total_fare' => 450.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($this->passengerA)
            ->get(route('passenger.bookings.ticket', $cancellableBooking));

        $response->assertStatus(200);
        $response->assertSee('Cancel Ticket');

        // Cancel it
        $cancellableBooking->update(['status' => Booking::STATUS_CANCELLED]);

        $responseCancelled = $this->actingAs($this->passengerA)
            ->get(route('passenger.bookings.ticket', $cancellableBooking));

        $responseCancelled->assertStatus(200);
        $responseCancelled->assertDontSee('Cancel Ticket');
    }
}
