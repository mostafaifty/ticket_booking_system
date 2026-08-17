<?php

namespace Tests\Feature;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStationCrudTest extends TestCase
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

    public function test_guest_cannot_access_station_management(): void
    {
        $response = $this->get(route('admin.stations.index'));
        $response->assertRedirect(route('login'));

        $createResponse = $this->get(route('admin.stations.create'));
        $createResponse->assertRedirect(route('login'));
    }

    public function test_passenger_cannot_access_station_management(): void
    {
        $response = $this->actingAs($this->passenger)->get(route('admin.stations.index'));
        $response->assertStatus(403);

        $createResponse = $this->actingAs($this->passenger)->get(route('admin.stations.create'));
        $createResponse->assertStatus(403);
    }

    public function test_admin_can_view_stations_index_with_list(): void
    {
        $stationA = Station::factory()->create(['name' => 'Dhaka Central', 'code' => 'DHK']);
        $stationB = Station::factory()->create(['name' => 'Chittagong Station', 'code' => 'CTG']);

        $response = $this->actingAs($this->admin)->get(route('admin.stations.index'));

        $response->assertStatus(200);
        $response->assertSee('Dhaka Central');
        $response->assertSee('DHK');
        $response->assertSee('Chittagong Station');
        $response->assertSee('CTG');
    }

    public function test_admin_can_search_and_filter_stations(): void
    {
        Station::factory()->create(['name' => 'Sylhet Railway Station', 'code' => 'SYL', 'status' => Station::STATUS_ACTIVE]);
        Station::factory()->create(['name' => 'Rajshahi Station', 'code' => 'RAJ', 'status' => Station::STATUS_INACTIVE]);

        // Search by keyword
        $searchResponse = $this->actingAs($this->admin)->get(route('admin.stations.index', ['search' => 'Sylhet']));
        $searchResponse->assertSee('Sylhet Railway Station');
        $searchResponse->assertDontSee('Rajshahi Station');

        // Filter by status
        $filterResponse = $this->actingAs($this->admin)->get(route('admin.stations.index', ['status' => 'inactive']));
        $filterResponse->assertSee('Rajshahi Station');
        $filterResponse->assertDontSee('Sylhet Railway Station');
    }

    public function test_admin_can_view_create_station_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.stations.create'));

        $response->assertStatus(200);
        $response->assertSee('Register New Railway Station');
    }

    public function test_admin_can_create_new_station_with_valid_data(): void
    {
        $stationData = [
            'name' => 'Cox\'s Bazar Iconic Station',
            'code' => 'cxb',
            'location' => 'Jhilongja, Cox\'s Bazar',
            'status' => Station::STATUS_ACTIVE,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.stations.store'), $stationData);

        $response->assertRedirect(route('admin.stations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stations', [
            'name' => 'Cox\'s Bazar Iconic Station',
            'code' => 'CXB', // Code transformed to uppercase
            'location' => 'Jhilongja, Cox\'s Bazar',
            'status' => Station::STATUS_ACTIVE,
        ]);
    }

    public function test_station_creation_fails_with_duplicate_code(): void
    {
        Station::factory()->create(['code' => 'DHK']);

        $response = $this->actingAs($this->admin)->post(route('admin.stations.store'), [
            'name' => 'Another Dhaka Station',
            'code' => 'DHK',
            'location' => 'Dhaka',
            'status' => Station::STATUS_ACTIVE,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_admin_can_view_station_details(): void
    {
        $station = Station::factory()->create(['name' => 'Khulna Station', 'code' => 'KLN']);

        $response = $this->actingAs($this->admin)->get(route('admin.stations.show', $station));

        $response->assertStatus(200);
        $response->assertSee('Khulna Station');
        $response->assertSee('KLN');
    }

    public function test_admin_can_view_edit_station_page(): void
    {
        $station = Station::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.stations.edit', $station));

        $response->assertStatus(200);
        $response->assertSee($station->name);
        $response->assertSee($station->code);
    }

    public function test_admin_can_update_station(): void
    {
        $station = Station::factory()->create([
            'name' => 'Old Station Name',
            'code' => 'OLD',
            'status' => Station::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.stations.update', $station), [
            'name' => 'Updated Station Name',
            'code' => 'OLD',
            'location' => 'Updated Location',
            'status' => Station::STATUS_INACTIVE,
        ]);

        $response->assertRedirect(route('admin.stations.index'));
        $response->assertSessionHas('success');

        $station->refresh();
        $this->assertEquals('Updated Station Name', $station->name);
        $this->assertEquals(Station::STATUS_INACTIVE, $station->status);
    }

    public function test_admin_can_delete_unreferenced_station(): void
    {
        $station = Station::factory()->create(['name' => 'Temporary Station', 'code' => 'TMP']);

        $response = $this->actingAs($this->admin)->delete(route('admin.stations.destroy', $station));

        $response->assertRedirect(route('admin.stations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('stations', [
            'id' => $station->id,
        ]);
    }

    public function test_cannot_delete_station_with_existing_schedules(): void
    {
        $stationA = Station::factory()->create();
        $stationB = Station::factory()->create();
        $train = Train::factory()->create();

        TrainSchedule::factory()->create([
            'train_id' => $train->id,
            'departure_station_id' => $stationA->id,
            'arrival_station_id' => $stationB->id,
        ]);

        // Attempt to delete stationA (which has departure schedule)
        $response = $this->actingAs($this->admin)->delete(route('admin.stations.destroy', $stationA));

        $response->assertRedirect(route('admin.stations.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('stations', [
            'id' => $stationA->id,
        ]);
    }
}
