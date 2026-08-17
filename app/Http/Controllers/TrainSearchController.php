<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchTrainRequest;
use App\Models\Station;
use App\Models\TrainSchedule;
use Illuminate\View\View;

class TrainSearchController extends Controller
{
    /**
     * Display train route search form and matching train results.
     */
    public function index(SearchTrainRequest $request): View
    {
        $stations = Station::orderBy('name')->get();
        $hasSearch = $request->filled('departure_station_id') || $request->filled('arrival_station_id') || $request->filled('journey_date');

        $query = TrainSchedule::with(['train', 'departureStation', 'arrivalStation', 'bookings'])
            ->whereIn('status', [TrainSchedule::STATUS_SCHEDULED, TrainSchedule::STATUS_DELAYED]);

        if ($request->filled('departure_station_id')) {
            $query->where('departure_station_id', $request->integer('departure_station_id'));
        }

        if ($request->filled('arrival_station_id')) {
            $query->where('arrival_station_id', $request->integer('arrival_station_id'));
        }

        if ($request->filled('journey_date')) {
            $query->whereDate('journey_date', $request->input('journey_date'));
        } else {
            // Default to upcoming journeys from today onward if no date specified
            $query->whereDate('journey_date', '>=', now()->toDateString());
        }

        $schedules = $query->orderBy('journey_date', 'asc')
            ->orderBy('departure_time', 'asc')
            ->paginate(10)
            ->withQueryString();

        $selectedDeparture = $request->input('departure_station_id');
        $selectedArrival = $request->input('arrival_station_id');
        $selectedDate = $request->input('journey_date');

        return view('trains.search', compact(
            'stations',
            'schedules',
            'hasSearch',
            'selectedDeparture',
            'selectedArrival',
            'selectedDate'
        ));
    }
}
