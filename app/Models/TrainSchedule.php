<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainSchedule extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_DELAYED = 'delayed';
    public const STATUS_DEPARTED = 'departed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'train_id',
        'departure_station_id',
        'arrival_station_id',
        'departure_time',
        'arrival_time',
        'journey_date',
        'fare',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'journey_date' => 'date',
            'fare' => 'decimal:2',
        ];
    }

    /**
     * Get the assigned train.
     */
    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    /**
     * Get the origin station.
     */
    public function departureStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'departure_station_id');
    }

    /**
     * Get the destination station.
     */
    public function arrivalStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'arrival_station_id');
    }

    /**
     * Get all bookings associated with this schedule.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Calculate booked seat count for this schedule.
     */
    public function getBookedSeatsCountAttribute(): int
    {
        return $this->bookings()->where('status', Booking::STATUS_CONFIRMED)->count();
    }

    /**
     * Calculate available seat count.
     */
    public function getAvailableSeatsCountAttribute(): int
    {
        $total = $this->train ? $this->train->total_seats : 0;
        return max(0, $total - $this->booked_seats_count);
    }

    /**
     * Check if a specific seat is already booked on this schedule.
     */
    public function isSeatBooked(int $seatId): bool
    {
        return $this->bookings()
            ->where('seat_id', $seatId)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->exists();
    }

    /**
     * Scope query to active / scheduled runs.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope query to search for train routes by stations and date.
     */
    public function scopeSearch($query, ?int $departureStationId = null, ?int $arrivalStationId = null, ?string $journeyDate = null)
    {
        if ($departureStationId) {
            $query->where('departure_station_id', $departureStationId);
        }

        if ($arrivalStationId) {
            $query->where('arrival_station_id', $arrivalStationId);
        }

        if ($journeyDate) {
            $query->whereDate('journey_date', $journeyDate);
        }

        return $query;
    }

    /**
     * Formatted departure time (e.g. 07:00 AM).
     */
    public function getFormattedDepartureTimeAttribute(): string
    {
        return $this->departure_time ? date('h:i A', strtotime($this->departure_time)) : 'N/A';
    }

    /**
     * Formatted arrival time (e.g. 12:30 PM).
     */
    public function getFormattedArrivalTimeAttribute(): string
    {
        return $this->arrival_time ? date('h:i A', strtotime($this->arrival_time)) : 'N/A';
    }

    /**
     * Formatted journey date (e.g. 17 Aug 2026).
     */
    public function getFormattedJourneyDateAttribute(): string
    {
        return $this->journey_date ? $this->journey_date->format('d M, Y') : 'N/A';
    }

    /**
     * Bootstrap / AdminLTE status badge color class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'badge-success',
            self::STATUS_DELAYED => 'badge-warning',
            self::STATUS_DEPARTED => 'badge-info',
            self::STATUS_COMPLETED => 'badge-secondary',
            self::STATUS_CANCELLED => 'badge-danger',
            default => 'badge-light',
        };
    }
}
