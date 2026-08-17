<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateSeatsRequest;
use App\Http\Requests\Admin\StoreSeatRequest;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\Train;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SeatController extends Controller
{
    /**
     * Display the seats list and coach layout for a specific train.
     */
    public function index(Train $train): View
    {
        $train->load('seats');

        $coaches = $train->seats->groupBy('coach');

        $allTrains = Train::orderBy('train_name')->get();

        return view('admin.seats.index', compact('train', 'coaches', 'allTrains'));
    }

    /**
     * Store a single newly created seat for the train.
     */
    public function store(StoreSeatRequest $request, Train $train): RedirectResponse
    {
        $seat = $train->seats()->create([
            'coach' => strtoupper(trim($request->input('coach'))),
            'seat_number' => trim($request->input('seat_number')),
            'seat_class' => $request->input('seat_class'),
        ]);

        $train->update(['total_seats' => $train->seats()->count()]);

        return redirect()->route('admin.trains.seats.index', $train)
            ->with('success', "Seat {$seat->label} added successfully!");
    }

    /**
     * Bulk generate seats for a coach on this train.
     */
    public function generate(GenerateSeatsRequest $request, Train $train): RedirectResponse
    {
        $coach = strtoupper(trim($request->input('coach')));
        $seatClass = $request->input('seat_class');
        $count = (int) $request->input('seat_count');
        $startNum = (int) ($request->input('start_number') ?: 1);

        $insertedCount = 0;

        DB::transaction(function () use ($train, $coach, $seatClass, $count, $startNum, &$insertedCount) {
            $existingNumbers = $train->seats()
                ->where('coach', $coach)
                ->pluck('seat_number')
                ->toArray();

            $newSeats = [];
            for ($i = 0; $i < $count; $i++) {
                $seatNumber = (string) ($startNum + $i);
                if (!in_array($seatNumber, $existingNumbers)) {
                    $newSeats[] = [
                        'train_id' => $train->id,
                        'coach' => $coach,
                        'seat_number' => $seatNumber,
                        'seat_class' => $seatClass,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $insertedCount++;
                }
            }

            if (!empty($newSeats)) {
                Seat::insert($newSeats);
            }

            $train->update(['total_seats' => $train->seats()->count()]);
        });

        return redirect()->route('admin.trains.seats.index', $train)
            ->with('success', "Successfully generated {$insertedCount} seat(s) for Coach {$coach} ({$seatClass}).");
    }

    /**
     * Remove the specified seat from the train.
     */
    public function destroy(Seat $seat): RedirectResponse
    {
        $train = $seat->train;

        if ($seat->bookings()->count() > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete seat {$seat->label} because it has historical booking records.");
        }

        $seat->delete();
        $train->update(['total_seats' => $train->seats()->count()]);

        return redirect()->back()
            ->with('success', "Seat {$seat->label} was deleted.");
    }
}
