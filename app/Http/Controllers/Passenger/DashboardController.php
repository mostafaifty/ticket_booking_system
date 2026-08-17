<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the passenger portal dashboard with live booking metrics, next upcoming trip, and quick actions.
     */
    public function index(): View
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // 1. Live booking statistics
        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'active_bookings' => $user->bookings()
                ->whereHas('trainSchedule', function ($q) use ($today) {
                    $q->whereDate('journey_date', '>=', $today);
                })
                ->where('bookings.status', Booking::STATUS_CONFIRMED)
                ->count(),
            'cancelled_bookings' => $user->bookings()
                ->where('bookings.status', Booking::STATUS_CANCELLED)
                ->count(),
        ];

        // 2. Next Upcoming Journey (closest upcoming confirmed trip)
        $upcomingJourney = $user->bookings()
            ->with([
                'trainSchedule.train',
                'trainSchedule.departureStation',
                'trainSchedule.arrivalStation',
                'seat',
                'passenger',
            ])
            ->whereHas('trainSchedule', function ($q) use ($today) {
                $q->whereDate('journey_date', '>=', $today);
            })
            ->join('train_schedules', 'bookings.train_schedule_id', '=', 'train_schedules.id')
            ->where('bookings.status', Booking::STATUS_CONFIRMED)
            ->orderBy('train_schedules.journey_date', 'asc')
            ->orderBy('train_schedules.departure_time', 'asc')
            ->select('bookings.*')
            ->first();

        // 3. Recent 5 bookings
        $recentBookings = $user->bookings()
            ->with([
                'trainSchedule.train',
                'trainSchedule.departureStation',
                'trainSchedule.arrivalStation',
                'seat',
                'passenger',
            ])
            ->latest('bookings.created_at')
            ->take(5)
            ->get();

        return view('passenger.dashboard', compact('user', 'stats', 'upcomingJourney', 'recentBookings'));
    }
}
