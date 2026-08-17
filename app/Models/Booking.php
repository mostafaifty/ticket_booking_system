<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'train_schedule_id',
        'seat_id',
        'booking_code',
        'booking_date',
        'total_fare',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'datetime',
            'total_fare' => 'decimal:2',
        ];
    }

    /**
     * Boot model to automatically generate unique booking code if not provided.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BK-' . strtoupper(Str::random(10));
            }
            if (empty($booking->booking_date)) {
                $booking->booking_date = now();
            }
        });
    }

    /**
     * Get the user who made this booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the train schedule for this booking.
     */
    public function trainSchedule(): BelongsTo
    {
        return $this->belongsTo(TrainSchedule::class);
    }

    /**
     * Get the reserved seat.
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Get the passenger info associated with this booking.
     */
    public function passenger(): HasOne
    {
        return $this->hasOne(Passenger::class);
    }

    /**
     * Scope query to confirmed bookings.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope query to cancelled bookings.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Check if this booking is currently eligible for cancellation.
     */
    public function isCancellable(): bool
    {
        if ($this->status !== self::STATUS_CONFIRMED) {
            return false;
        }

        if (!$this->trainSchedule) {
            return false;
        }

        if (in_array($this->trainSchedule->status, [
            TrainSchedule::STATUS_CANCELLED,
            TrainSchedule::STATUS_COMPLETED,
            TrainSchedule::STATUS_DEPARTED,
        ])) {
            return false;
        }

        $journeyDate = $this->trainSchedule->journey_date ? $this->trainSchedule->journey_date->format('Y-m-d') : null;
        $departureTime = $this->trainSchedule->departure_time ?: '00:00:00';

        if (!$journeyDate) {
            return false;
        }

        $departureDateTime = \Carbon\Carbon::parse("{$journeyDate} {$departureTime}");

        return $departureDateTime->isFuture();
    }

    /**
     * Formatted booking date (e.g. 17 Aug, 2026 - 09:30 PM).
     */
    public function getFormattedBookingDateAttribute(): string
    {
        return $this->booking_date ? $this->booking_date->format('d M, Y - h:i A') : 'N/A';
    }

    /**
     * Formatted total fare in BDT (৳).
     */
    public function getFormattedFareAttribute(): string
    {
        return '৳ ' . number_format($this->total_fare, 2);
    }

    /**
     * Bootstrap / AdminLTE status badge color class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'badge-success',
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_CANCELLED => 'badge-danger',
            self::STATUS_REFUNDED => 'badge-info',
            default => 'badge-secondary',
        };
    }
}
