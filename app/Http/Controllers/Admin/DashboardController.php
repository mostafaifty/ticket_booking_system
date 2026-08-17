<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the AdminLTE admin dashboard.
     */
    public function index(): View
    {
        // Placeholder summary stats to be populated by domain models in future increments
        $stats = [
            'total_trains' => 0,
            'total_stations' => 0,
            'active_schedules' => 0,
            'total_bookings' => 0,
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
