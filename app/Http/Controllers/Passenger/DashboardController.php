<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the passenger portal dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'total_bookings' => 0,
            'upcoming_trips' => 0,
            'completed_trips' => 0,
            'cancelled_trips' => 0,
        ];

        return view('passenger.dashboard', compact('user', 'stats'));
    }
}
