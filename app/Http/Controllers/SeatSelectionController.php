<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectSeatRequest;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\TrainSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SeatSelectionController extends Controller
{
    /**
     * Display real-time visual seat map for the selected train schedule.
     */
    public function index(TrainSchedule $schedule): View
    {
        $schedule->load(['train.seats', 'departureStation', 'arrivalStation']);

        $bookedSeatIds = $schedule->bookings()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->pluck('seat_id')
            ->toArray();

        // Group train seats by coach
        $coaches = $schedule->train->seats->groupBy('coach');

        return view('schedules.seats', compact('schedule', 'coaches', 'bookedSeatIds'));
    }

    /**
     * Handle passenger seat selection and transaction-safe booking reservation.
     */
    public function select(SelectSeatRequest $request, TrainSchedule $schedule): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please log in or register an account to confirm your seat booking.');
        }

        $seatId = (int) $request->validated('seat_id');

        try {
            $booking = DB::transaction(function () use ($user, $schedule, $seatId, $request) {
                // Verify that seat belongs to this schedule's train
                $seat = Seat::where('id', $seatId)
                    ->where('train_id', $schedule->train_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Prevent duplicate seat booking for the same schedule (pessimistic lock)
                $isAlreadyBooked = Booking::where('train_schedule_id', $schedule->id)
                    ->where('seat_id', $seat->id)
                    ->where('status', Booking::STATUS_CONFIRMED)
                    ->lockForUpdate()
                    ->exists();

                if ($isAlreadyBooked) {
                    throw new \Exception("Seat {$seat->label} has already been booked for this schedule. Please select another seat.");
                }

                // Create confirmed booking
                $newBooking = Booking::create([
                    'user_id' => $user->id,
                    'train_schedule_id' => $schedule->id,
                    'seat_id' => $seat->id,
                    'total_fare' => $schedule->fare,
                    'status' => Booking::STATUS_CONFIRMED,
                ]);

                // Create passenger details
                $newBooking->passenger()->create([
                    'name' => $request->validated('passenger_name'),
                    'phone' => $request->validated('passenger_phone'),
                    'nid_or_passport' => $request->validated('nid_or_passport'),
                    'age' => $request->validated('age'),
                    'gender' => $request->validated('gender'),
                ]);

                return $newBooking;
            });

            return redirect()->route('passenger.dashboard')
                ->with('success', "Seat reserved successfully! Booking Code: {$booking->booking_code} on {$schedule->train->train_name}.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
