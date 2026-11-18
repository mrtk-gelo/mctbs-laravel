<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingReceipt extends Model
{
    protected $table = 'booking_receipts';

    protected $primaryKey = 'receipt_id';

    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'receipt_number',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
