<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the AdminLTE admin dashboard with live database metrics, recent activity, and quick management links.
     */
    public function index(): View
    {
        // 1. Efficient aggregated system metrics
        $stats = [
            'total_passengers' => User::where('role', User::ROLE_PASSENGER)->count(),
            'total_trains' => Train::count(),
            'total_stations' => Station::count(),
            'total_schedules' => TrainSchedule::count(),
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => Booking::where('status', Booking::STATUS_CONFIRMED)->count(),
            'cancelled_bookings' => Booking::where('status', Booking::STATUS_CANCELLED)->count(),
        ];

        // 2. Recent bookings with eager-loaded relations (avoids N+1 query problem)
        $recentBookings = Booking::with([
            'user',
            'trainSchedule.train',
            'trainSchedule.departureStation',
            'trainSchedule.arrivalStation',
            'seat',
            'passenger',
        ])
        ->latest()
        ->take(10)
        ->get();

        // 3. Fetch trains for quick seat management links
        $trains = Train::select('id', 'train_name', 'train_number', 'total_seats')
            ->orderBy('train_name')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'trains'));
    }
}
