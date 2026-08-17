<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Station\StoreStationRequest;
use App\Http\Requests\Admin\Station\UpdateStationRequest;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StationController extends Controller
{
    /**
     * Display a listing of the stations with search and filtering.
     */
    public function index(Request $request): View
    {
        $query = Station::withCount(['departureSchedules', 'arrivalSchedules']);

        // Search filter: name, code, or location
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Status filter: active, inactive
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $stations = $query->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.stations.index', compact('stations'));
    }

    /**
     * Show the form for creating a new station.
     */
    public function create(): View
    {
        return view('admin.stations.create');
    }

    /**
     * Store a newly created station in database.
     */
    public function store(StoreStationRequest $request): RedirectResponse
    {
        $station = Station::create($request->validated());

        return redirect()->route('admin.stations.index')
            ->with('success', "Station '{$station->name}' ({$station->code}) has been created successfully.");
    }

    /**
     * Display the specified station details and schedule metrics.
     */
    public function show(Station $station): View
    {
        $station->loadCount(['departureSchedules', 'arrivalSchedules']);
        
        $departureSchedules = $station->departureSchedules()
            ->with('train', 'arrivalStation')
            ->latest('journey_date')
            ->take(5)
            ->get();

        $arrivalSchedules = $station->arrivalSchedules()
            ->with('train', 'departureStation')
            ->latest('journey_date')
            ->take(5)
            ->get();

        return view('admin.stations.show', compact('station', 'departureSchedules', 'arrivalSchedules'));
    }

    /**
     * Show the form for editing the specified station.
     */
    public function edit(Station $station): View
    {
        return view('admin.stations.edit', compact('station'));
    }

    /**
     * Update the specified station in database.
     */
    public function update(UpdateStationRequest $request, Station $station): RedirectResponse
    {
        $station->update($request->validated());

        return redirect()->route('admin.stations.index')
            ->with('success', "Station '{$station->name}' ({$station->code}) has been updated successfully.");
    }

    /**
     * Remove the specified station from database with reference validation.
     */
    public function destroy(Station $station): RedirectResponse
    {
        $departureCount = $station->departureSchedules()->count();
        $arrivalCount = $station->arrivalSchedules()->count();

        if ($departureCount > 0 || $arrivalCount > 0) {
            return redirect()->route('admin.stations.index')
                ->with('error', "Cannot delete station '{$station->name}' ({$station->code}) because it is actively referenced in {$departureCount} departure schedule(s) and {$arrivalCount} arrival schedule(s).");
        }

        $stationName = $station->name;
        $stationCode = $station->code;
        $station->delete();

        return redirect()->route('admin.stations.index')
            ->with('success', "Station '{$stationName}' ({$stationCode}) was deleted successfully.");
    }
}
