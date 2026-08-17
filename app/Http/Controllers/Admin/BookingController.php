<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of all passenger bookings across the railway system.
     */
    public function index(Request $request): View
    {
        $query = Booking::with([
            'user',
            'trainSchedule.train',
            'trainSchedule.departureStation',
            'trainSchedule.arrivalStation',
            'seat',
            'passenger',
        ]);

        if ($request->filled('pnr')) {
            $pnr = trim($request->input('pnr'));
            $query->where('booking_code', 'like', "%{$pnr}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('train_id')) {
            $trainId = $request->integer('train_id');
            $query->whereHas('trainSchedule', function ($q) use ($trainId) {
                $q->where('train_id', $trainId);
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $trains = Train::orderBy('train_name')->get();

        return view('admin.bookings.index', compact('bookings', 'trains'));
    }
}
