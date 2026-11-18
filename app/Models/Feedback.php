<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $primaryKey = 'feedback_id';

    protected $fillable = [
        'booking_id',
        'user_id',
        'unit_id',
        'rating',
        'review',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }
}
