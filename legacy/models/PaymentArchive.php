<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentArchive extends Model
{
    // Laravel would guess 'payment_archives' — actual table is 'payments_archive'
    protected $table = 'payments_archive';

    public $timestamps = false;

    protected $fillable = [
        'booking_id', 'payment_type', 'payment_method', 'amount',
        'reference_number', 'receipt_path', 'num_guests', 'checkout_date',
        'notes', 'status', 'verified_by', 'verified_at', 'archived_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'checkout_date' => 'date',
        'verified_at' => 'datetime',
        'archived_at' => 'datetime',
    ];
}
