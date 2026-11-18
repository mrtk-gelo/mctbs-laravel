<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'booking_id', 'receipt_number', 'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
