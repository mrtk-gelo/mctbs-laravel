<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDamage extends Model
{
    protected $table = 'booking_damages';

    protected $primaryKey = 'booking_damage_id';

    public $timestamps = false;

    protected $fillable = ['booking_id', 'name', 'price', 'quantity'];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
