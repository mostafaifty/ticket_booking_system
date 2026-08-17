<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\TrainSchedule;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class TicketCancellationService
{
    /**
     * Cancel an eligible railway booking and release the seat in a database transaction.
     *
     * @param  Booking  $booking
     * @return Booking
     *
     * @throws Exception
     */
    public function cancelBooking(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            // Lock booking row to prevent race-condition duplicate cancellation
            $lockedBooking = Booking::with(['trainSchedule', 'seat', 'passenger'])
                ->where('id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 1. Prevent duplicate cancellation
            if ($lockedBooking->status === Booking::STATUS_CANCELLED) {
                throw new Exception("This booking (PNR: {$lockedBooking->booking_code}) has already been cancelled.");
            }

            // 2. Prevent cancellation if booking is not in confirmed status
            if ($lockedBooking->status !== Booking::STATUS_CONFIRMED) {
                throw new Exception("Only confirmed bookings can be cancelled. Current status: {$lockedBooking->status}.");
            }

            // 3. Verify schedule validity
            $schedule = $lockedBooking->trainSchedule;
            if (!$schedule) {
                throw new Exception("Associated train schedule could not be found.");
            }

            if (in_array($schedule->status, [
                TrainSchedule::STATUS_CANCELLED,
                TrainSchedule::STATUS_COMPLETED,
                TrainSchedule::STATUS_DEPARTED,
            ])) {
                throw new Exception("Cannot cancel ticket: the train schedule is currently marked as '{$schedule->status}'.");
            }

            // 4. Validate departure time is in the future
            $journeyDate = $schedule->journey_date ? $schedule->journey_date->format('Y-m-d') : null;
            $departureTime = $schedule->departure_time ?: '00:00:00';

            if ($journeyDate) {
                $departureDateTime = Carbon::parse("{$journeyDate} {$departureTime}");
                if ($departureDateTime->isPast()) {
                    throw new Exception("Cannot cancel ticket: the scheduled departure time has already passed.");
                }
            }

            // 5. Update status to cancelled (releasing the seat while preserving the booking record)
            $lockedBooking->update([
                'status' => Booking::STATUS_CANCELLED,
            ]);

            return $lockedBooking;
        });
    }
}
