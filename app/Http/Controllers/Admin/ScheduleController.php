<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScheduleRequest;
use App\Http\Requests\Admin\UpdateScheduleRequest;
use App\Models\Booking;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Display a paginated listing of all train schedules with filtering.
     */
    public function index(Request $request): View
    {
        $query = TrainSchedule::with(['train', 'departureStation', 'arrivalStation', 'bookings']);

        if ($request->filled('train_id')) {
            $query->where('train_id', $request->integer('train_id'));
        }

        if ($request->filled('departure_station_id')) {
            $query->where('departure_station_id', $request->integer('departure_station_id'));
        }

        if ($request->filled('arrival_station_id')) {
            $query->where('arrival_station_id', $request->integer('arrival_station_id'));
        }

        if ($request->filled('journey_date')) {
            $query->whereDate('journey_date', $request->input('journey_date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $schedules = $query->orderBy('journey_date', 'desc')
            ->orderBy('departure_time', 'asc')
            ->paginate(15)
            ->withQueryString();

        $trains = Train::orderBy('train_name')->get();
        $stations = Station::orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'trains', 'stations'));
    }

    /**
     * Show the form for creating a new train schedule.
     */
    public function create(): View
    {
        $trains = Train::where('status', Train::STATUS_ACTIVE)->orderBy('train_name')->get();
        $stations = Station::orderBy('name')->get();

        return view('admin.schedules.create', compact('trains', 'stations'));
    }

    /**
     * Store a newly created train schedule in storage.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $schedule = TrainSchedule::create($request->validated());

        return redirect()->route('admin.schedules.index')
            ->with('success', "Train schedule #{$schedule->id} ({$schedule->train->train_name}) created successfully!");
    }

    /**
     * Display the specified train schedule with bookings and seat stats.
     */
    public function show(TrainSchedule $schedule): View
    {
        $schedule->load([
            'train.seats',
            'departureStation',
            'arrivalStation',
            'bookings.user',
            'bookings.seat',
            'bookings.passenger',
        ]);

        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified train schedule.
     */
    public function edit(TrainSchedule $schedule): View
    {
        $trains = Train::orderBy('train_name')->get();
        $stations = Station::orderBy('name')->get();

        return view('admin.schedules.edit', compact('schedule', 'trains', 'stations'));
    }

    /**
     * Update the specified train schedule in storage.
     */
    public function update(UpdateScheduleRequest $request, TrainSchedule $schedule): RedirectResponse
    {
        $schedule->update($request->validated());

        return redirect()->route('admin.schedules.index')
            ->with('success', "Train schedule #{$schedule->id} updated successfully!");
    }

    /**
     * Remove the specified train schedule from storage.
     */
    public function destroy(TrainSchedule $schedule): RedirectResponse
    {
        $confirmedBookingsCount = $schedule->bookings()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->count();

        if ($confirmedBookingsCount > 0) {
            return redirect()->route('admin.schedules.index')
                ->with('error', "Cannot delete schedule #{$schedule->id} because it has {$confirmedBookingsCount} confirmed passenger booking(s). Change status to 'Cancelled' instead.");
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', "Schedule #{$schedule->id} was removed successfully.");
    }
}
