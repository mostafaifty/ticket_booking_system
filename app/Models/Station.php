<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
    ];

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
