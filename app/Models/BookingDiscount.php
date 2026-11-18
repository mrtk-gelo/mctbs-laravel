<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDiscount extends Model
{
    protected $table = 'booking_discounts';

    protected $primaryKey = 'discount_id';

    protected $fillable = [
        'booking_id',
        'booking_guest_id',
        'verified_by',
        'discount_type',
        'id_reference',
        'verification_status',
        'verification_note',
        'verified_at',
        'eligible_amount',
        'discount_rate',
        'discount_amount',
        'final_amount',
        'refund_amount',
        'refund_status',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'eligible_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(BookingGuest::class, 'booking_guest_id', 'booking_guest_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }
}
