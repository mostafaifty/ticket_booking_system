<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketBookingRequest;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\TrainSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a paginated listing of the authenticated passenger's bookings.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'current'); // 'current', 'past', 'all'
        $today = now()->toDateString();

        // Calculate counts for tabs
        $currentCount = $user->bookings()
            ->whereHas('trainSchedule', function ($q) use ($today) {
                $q->whereDate('journey_date', '>=', $today);
            })
            ->where('status', Booking::STATUS_CONFIRMED)
            ->count();

        $pastCount = $user->bookings()
            ->where(function ($query) use ($today) {
                $query->whereHas('trainSchedule', function ($q) use ($today) {
                    $q->whereDate('journey_date', '<', $today);
                })->orWhereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_REFUNDED]);
            })
            ->count();

        $totalCount = $user->bookings()->count();

        // Base query with relations
        $query = $user->bookings()
            ->with([
                'trainSchedule.train',
                'trainSchedule.departureStation',
                'trainSchedule.arrivalStation',
                'seat',
                'passenger',
            ]);

        // Apply Tab Filter
        if ($tab === 'current') {
            $query->whereHas('trainSchedule', function ($q) use ($today) {
                $q->whereDate('journey_date', '>=', $today);
            })->where('status', Booking::STATUS_CONFIRMED);
        } elseif ($tab === 'past') {
            $query->where(function ($q) use ($today) {
                $q->whereHas('trainSchedule', function ($tq) use ($today) {
                    $tq->whereDate('journey_date', '<', $today);
                })->orWhereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_REFUNDED]);
            });
        }

        // Search by PNR or Train Name
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhereHas('trainSchedule.train', function ($tq) use ($search) {
                        $tq->where('train_name', 'like', "%{$search}%")
                            ->orWhere('train_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trainSchedule.departureStation', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trainSchedule.arrivalStation', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('passenger.bookings.index', compact(
            'bookings',
            'tab',
            'currentCount',
            'pastCount',
            'totalCount'
        ));
    }

    /**
     * Display the specified booking details and printable ticket.
     */
    public function show(Booking $booking): View
    {
        return $this->renderTicketView($booking);
    }

    /**
     * Dedicated printable ticket slip view.
     */
    public function ticket(Booking $booking): View
    {
        return $this->renderTicketView($booking);
    }

    /**
     * Helper to load relations, authorize, and render ticket view.
     */
    protected function renderTicketView(Booking $booking): View
    {
        $user = Auth::user();

        // Authorization check: User must own the booking or be an admin
        if (!$user->isAdmin() && $booking->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this booking ticket.');
        }

        $booking->load([
            'trainSchedule.train',
            'trainSchedule.departureStation',
            'trainSchedule.arrivalStation',
            'seat',
            'passenger',
            'user',
        ]);

        return view('passenger.bookings.ticket', compact('booking'));
    }

    /**
     * Store a new railway ticket booking in a database transaction with concurrency locking.
     */
    public function store(StoreTicketBookingRequest $request, TrainSchedule $schedule): RedirectResponse
    {
        $user = Auth::user();

        // 1. Verify schedule operational status
        if (in_array($schedule->status, [TrainSchedule::STATUS_CANCELLED, TrainSchedule::STATUS_COMPLETED, TrainSchedule::STATUS_DEPARTED])) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Cannot complete reservation: this train schedule is currently marked as '{$schedule->status}'.");
        }

        $seatId = (int) $request->validated('seat_id');

        try {
            $booking = DB::transaction(function () use ($user, $schedule, $seatId, $request) {
                // 2. Lock and verify seat belongs to the assigned train
                $seat = Seat::where('id', $seatId)
                    ->where('train_id', $schedule->train_id)
                    ->lockForUpdate()
                    ->first();

                if (!$seat) {
                    throw new \Exception("The selected seat does not belong to the train assigned for this schedule.");
                }

                // 3. Re-verify seat availability with pessimistic concurrency lock (Prevent race conditions)
                $isAlreadyBooked = Booking::where('train_schedule_id', $schedule->id)
                    ->where('seat_id', $seat->id)
                    ->where('status', Booking::STATUS_CONFIRMED)
                    ->lockForUpdate()
                    ->exists();

                if ($isAlreadyBooked) {
                    throw new \Exception("Seat {$seat->label} has already been reserved by another passenger. Please select another seat.");
                }

                // 4. Calculate fare strictly on the server
                $totalFare = (float) $schedule->fare;

                // 5. Create Booking record
                $newBooking = Booking::create([
                    'user_id' => $user->id,
                    'train_schedule_id' => $schedule->id,
                    'seat_id' => $seat->id,
                    'booking_date' => now(),
                    'total_fare' => $totalFare,
                    'status' => Booking::STATUS_CONFIRMED,
                ]);

                // 6. Create associated Passenger record
                $newBooking->passenger()->create([
                    'name' => trim($request->validated('passenger_name')),
                    'phone' => trim($request->validated('passenger_phone')),
                    'nid_or_passport' => $request->validated('nid_or_passport'),
                    'age' => $request->validated('age'),
                    'gender' => $request->validated('gender'),
                ]);

                return $newBooking;
            });

            return redirect()->route('bookings.confirmation', $booking)
                ->with('success', "Ticket booked successfully! PNR: {$booking->booking_code}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the printable e-ticket booking confirmation voucher.
     */
    public function confirmation(Booking $booking): View
    {
        $user = Auth::user();

        // Ensure authorization: Only owner or admin can view the booking voucher
        if (!$user->isAdmin() && $booking->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this booking voucher.');
        }

        $booking->load([
            'trainSchedule.train',
            'trainSchedule.departureStation',
            'trainSchedule.arrivalStation',
            'seat',
            'passenger',
            'user',
        ]);

        return view('bookings.confirmation', compact('booking'));
    }

    /**
     * Cancel an eligible railway booking and release its seat.
     */
    public function cancel(Request $request, \App\Services\TicketCancellationService $cancellationService, Booking $booking): RedirectResponse
    {
        $user = Auth::user();

        // Authorization check: User must own the booking or be an admin
        if (!$user->isAdmin() && $booking->user_id !== $user->id) {
            abort(403, 'Unauthorized access to cancel this booking.');
        }

        try {
            $cancelledBooking = $cancellationService->cancelBooking($booking);

            return redirect()->back()
                ->with('success', "Booking {$cancelledBooking->booking_code} cancelled successfully. Seat {$cancelledBooking->seat->coach}-{$cancelledBooking->seat->seat_number} has been released.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
