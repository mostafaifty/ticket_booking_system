<?php

namespace App\Http\Controllers\Passenger;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the passenger portal dashboard with live booking metrics.
     */
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'upcoming_trips' => $user->bookings()->where('status', Booking::STATUS_CONFIRMED)->count(),
            'completed_trips' => 0,
            'cancelled_trips' => $user->bookings()->where('status', Booking::STATUS_CANCELLED)->count(),
        ];

        return view('passenger.dashboard', compact('user', 'stats'));
    }
}
