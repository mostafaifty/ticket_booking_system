<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passenger extends Model
{
    use HasFactory;

    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';
    public const GENDER_OTHER = 'other';

    protected $fillable = [
        'booking_id',
        'name',
        'phone',
        'nid_or_passport',
        'age',
        'gender',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
        ];
    }

    /**
     * Get the booking this passenger belongs to.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
