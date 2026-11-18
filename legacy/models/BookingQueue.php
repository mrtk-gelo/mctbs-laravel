<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingQueue extends Model
{
    // Laravel would guess 'booking_queues' — actual table is singular
    protected $table = 'booking_queue';

    public $timestamps = false;

    protected $fillable = [
        'unit_id', 'check_in', 'check_out', 'booking_id', 'queue_expires_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'queue_expires_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(TransientUnit::class, 'unit_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
