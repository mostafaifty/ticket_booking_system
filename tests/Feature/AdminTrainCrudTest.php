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

class AdminTrainCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $passenger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->passenger = User::factory()->create([
            'role' => User::ROLE_PASSENGER,
        ]);
    }

    public function test_guest_cannot_access_train_management(): void
    {
        $response = $this->get(route('admin.trains.index'));
        $response->assertRedirect(route('login'));

        $createResponse = $this->get(route('admin.trains.create'));
        $createResponse->assertRedirect(route('login'));
    }

    public function test_passenger_cannot_access_train_management(): void
    {
        $response = $this->actingAs($this->passenger)->get(route('admin.trains.index'));
        $response->assertStatus(403);

        $createResponse = $this->actingAs($this->passenger)->get(route('admin.trains.create'));
        $createResponse->assertStatus(403);
    }

    public function test_admin_can_view_trains_index_with_list(): void
    {
        Train::factory()->create(['train_number' => '701', 'train_name' => 'Subarna Express']);
        Train::factory()->create(['train_number' => '703', 'train_name' => 'Mohanagar Provati']);

        $response = $this->actingAs($this->admin)->get(route('admin.trains.index'));

        $response->assertStatus(200);
        $response->assertSee('Subarna Express');
        $response->assertSee('#701');
        $response->assertSee('Mohanagar Provati');
        $response->assertSee('#703');
    }

    public function test_admin_can_search_and_filter_trains(): void
    {
        Train::factory()->create([
            'train_number' => '709',
            'train_name' => 'Parabat Express',
            'train_type' => Train::TYPE_INTERCITY,
            'status' => Train::STATUS_ACTIVE,
        ]);

        Train::factory()->create([
            'train_number' => '401',
            'train_name' => 'Karnaphuli Commuter',
            'train_type' => Train::TYPE_COMMUTER,
            'status' => Train::STATUS_MAINTENANCE,
        ]);

        // Search by train name
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.trains.index', ['search' => 'Parabat']));
        $searchResponse->assertSee('Parabat Express');
        $searchResponse->assertDontSee('Karnaphuli Commuter');

        // Filter by train type
        $typeFilterResponse = $this->actingAs($this->admin)->get(route('admin.trains.index', ['train_type' => 'Commuter']));
        $typeFilterResponse->assertSee('Karnaphuli Commuter');
        $typeFilterResponse->assertDontSee('Parabat Express');

        // Filter by status
        $statusFilterResponse = $this->actingAs($this->admin)->get(route('admin.trains.index', ['status' => 'maintenance']));
        $statusFilterResponse->assertSee('Karnaphuli Commuter');
        $statusFilterResponse->assertDontSee('Parabat Express');
    }

    public function test_admin_can_view_create_train_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.trains.create'));

        $response->assertStatus(200);
        $response->assertSee('Add Train to Fleet');
    }

    public function test_admin_can_create_new_train_with_valid_data(): void
    {
        $trainData = [
            'train_number' => '813',
            'train_name' => 'Cox\'s Bazar Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 450,
            'status' => Train::STATUS_ACTIVE,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.trains.store'), $trainData);

        $response->assertRedirect(route('admin.trains.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trains', [
            'train_number' => '813',
            'train_name' => 'Cox\'s Bazar Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 450,
            'status' => Train::STATUS_ACTIVE,
        ]);
    }

    public function test_train_creation_fails_with_duplicate_train_number(): void
    {
        Train::factory()->create(['train_number' => '701']);

        $response = $this->actingAs($this->admin)->post(route('admin.trains.store'), [
            'train_number' => '701',
            'train_name' => 'Duplicate Express',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 300,
            'status' => Train::STATUS_ACTIVE,
        ]);

        $response->assertSessionHasErrors(['train_number']);
    }

    public function test_admin_can_view_train_details(): void
    {
        $train = Train::factory()->create([
            'train_number' => '753',
            'train_name' => 'Silk City Express',
        ]);

        Seat::factory()->create([
            'train_id' => $train->id,
            'coach' => 'KA',
            'seat_number' => '1',
            'seat_class' => Seat::CLASS_SNIGDHA,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.trains.show', $train));

        $response->assertStatus(200);
        $response->assertSee('Silk City Express');
        $response->assertSee('753');
        $response->assertSee('Coach KA');
        $response->assertSee(Seat::CLASS_SNIGDHA);
    }

    public function test_admin_can_view_edit_train_page(): void
    {
        $train = Train::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.trains.edit', $train));

        $response->assertStatus(200);
        $response->assertSee($train->train_name);
        $response->assertSee($train->train_number);
    }

    public function test_admin_can_update_train(): void
    {
        $train = Train::factory()->create([
            'train_number' => '705',
            'train_name' => 'Ekota Express',
            'total_seats' => 350,
            'status' => Train::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.trains.update', $train), [
            'train_number' => '705',
            'train_name' => 'Ekota Express Updated',
            'train_type' => Train::TYPE_INTERCITY,
            'total_seats' => 400,
            'status' => Train::STATUS_MAINTENANCE,
        ]);

        $response->assertRedirect(route('admin.trains.index'));
        $response->assertSessionHas('success');

        $train->refresh();
        $this->assertEquals('Ekota Express Updated', $train->train_name);
        $this->assertEquals(400, $train->total_seats);
        $this->assertEquals(Train::STATUS_MAINTENANCE, $train->status);
    }

    public function test_admin_can_delete_unreferenced_train(): void
    {
        $train = Train::factory()->create(['train_number' => '999']);
        Seat::factory()->create(['train_id' => $train->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.trains.destroy', $train));

        $response->assertRedirect(route('admin.trains.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('trains', [
            'id' => $train->id,
        ]);
        $this->assertDatabaseMissing('seats', [
            'train_id' => $train->id,
        ]);
    }

    public function test_cannot_delete_train_with_existing_schedules(): void
    {
        $stationA = Station::factory()->create();
        $stationB = Station::factory()->create();
        $train = Train::factory()->create();

        TrainSchedule::factory()->create([
            'train_id' => $train->id,
            'departure_station_id' => $stationA->id,
            'arrival_station_id' => $stationB->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.trains.destroy', $train));

        $response->assertRedirect(route('admin.trains.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('trains', [
            'id' => $train->id,
        ]);
    }
}
