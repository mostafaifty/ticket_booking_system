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
}
