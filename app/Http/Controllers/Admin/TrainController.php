<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Train\StoreTrainRequest;
use App\Http\Requests\Admin\Train\UpdateTrainRequest;
use App\Models\Train;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrainController extends Controller
{
    /**
     * Display a listing of the trains with search and filtering.
     */
    public function index(Request $request): View
    {
        $query = Train::withCount(['seats', 'schedules']);

        // Search filter: train name or train number
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('train_name', 'like', "%{$search}%")
                    ->orWhere('train_number', 'like', "%{$search}%");
            });
        }

        // Train type filter
        if ($request->filled('train_type')) {
            $query->where('train_type', $request->input('train_type'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $trains = $query->orderBy('train_number', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.trains.index', compact('trains'));
    }

    /**
     * Show the form for creating a new train.
     */
    public function create(): View
    {
        return view('admin.trains.create');
    }

    /**
     * Store a newly created train in database.
     */
    public function store(StoreTrainRequest $request): RedirectResponse
    {
        $train = Train::create($request->validated());

        return redirect()->route('admin.trains.index')
            ->with('success', "Train '{$train->train_name}' (#{$train->train_number}) has been created successfully.");
    }

    /**
     * Display the specified train details, coach breakdown, and schedules.
     */
    public function show(Train $train): View
    {
        $train->loadCount(['seats', 'schedules']);

        $coachBreakdown = $train->seats()
            ->select('coach', 'seat_class', DB::raw('count(*) as total'))
            ->groupBy('coach', 'seat_class')
            ->orderBy('coach')
            ->get();

        $recentSchedules = $train->schedules()
            ->with(['departureStation', 'arrivalStation'])
            ->latest('journey_date')
            ->take(5)
            ->get();

        return view('admin.trains.show', compact('train', 'coachBreakdown', 'recentSchedules'));
    }

    /**
     * Show the form for editing the specified train.
     */
    public function edit(Train $train): View
    {
        return view('admin.trains.edit', compact('train'));
    }

    /**
     * Update the specified train in database.
     */
    public function update(UpdateTrainRequest $request, Train $train): RedirectResponse
    {
        $train->update($request->validated());

        return redirect()->route('admin.trains.index')
            ->with('success', "Train '{$train->train_name}' (#{$train->train_number}) has been updated successfully.");
    }

    /**
     * Remove the specified train from database with reference validation.
     */
    public function destroy(Train $train): RedirectResponse
    {
        $schedulesCount = $train->schedules()->count();
        $bookedSeatsCount = $train->seats()->whereHas('bookings')->count();

        if ($schedulesCount > 0 || $bookedSeatsCount > 0) {
            return redirect()->route('admin.trains.index')
                ->with('error', "Cannot delete train '{$train->train_name}' (#{$train->train_number}) because it has {$schedulesCount} scheduled run(s) and {$bookedSeatsCount} seat(s) with existing booking records.");
        }

        $trainName = $train->train_name;
        $trainNumber = $train->train_number;

        // Safely delete unbooked seats assigned to this train
        $train->seats()->delete();
        $train->delete();

        return redirect()->route('admin.trains.index')
            ->with('success', "Train '{$trainName}' (#{$trainNumber}) was deleted successfully.");
    }
}
