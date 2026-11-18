<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalCharge extends Model
{
    protected $table = 'additional_charges';

    protected $primaryKey = 'additional_charge_id';

    protected $fillable = [
        'booking_id',
        'created_by',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
