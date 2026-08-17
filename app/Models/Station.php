<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'code',
        'location',
        'status',
    ];

    /**
     * Scope query to only active stations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Check if station is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Schedules where this station is the departure point.
     */
    public function departureSchedules(): HasMany
    {
        return $this->hasMany(TrainSchedule::class, 'departure_station_id');
    }

    /**
     * Schedules where this station is the arrival destination.
     */
    public function arrivalSchedules(): HasMany
    {
        return $this->hasMany(TrainSchedule::class, 'arrival_station_id');
    }
}
