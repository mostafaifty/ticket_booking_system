<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainSchedule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the AdminLTE admin dashboard with live database metrics.
     */
    public function index(): View
    {
        $stats = [
            'total_trains' => Train::count(),
            'total_stations' => Station::count(),
            'active_schedules' => TrainSchedule::scheduled()->count(),
            'total_bookings' => Booking::confirmed()->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
