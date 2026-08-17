<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    use HasFactory;

    public const CLASS_AC_BERTH = 'AC_BERTH';
    public const CLASS_SNIGDHA = 'SNIGDHA';
    public const CLASS_SHOVON_CHAIR = 'SHOVON_CHAIR';
    public const CLASS_SHOVON = 'SHOVON';
    public const CLASS_FIRST_CLASS = 'FIRST_CLASS';

    protected $fillable = [
        'train_id',
        'seat_number',
        'coach',
        'seat_class',
    ];

    /**
     * Get the train that owns this seat.
     */
    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    /**
     * Get all bookings made for this seat.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get formatted seat label e.g., "KA-12 (SHOVON_CHAIR)".
     */
    public function getLabelAttribute(): string
    {
        return "{$this->coach}-{$this->seat_number} ({$this->seat_class})";
    }
}
