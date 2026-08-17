<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Train extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const TYPE_INTERCITY = 'Intercity';
    public const TYPE_MAIL_EXPRESS = 'Mail/Express';
    public const TYPE_COMMUTER = 'Commuter';

    protected $fillable = [
        'train_number',
        'train_name',
        'train_type',
        'total_seats',
        'status',
    ];

    /**
     * Get the seats belonging to this train.
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Get the schedules assigned to this train.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(TrainSchedule::class);
    }

    /**
     * Scope query to only active trains.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
