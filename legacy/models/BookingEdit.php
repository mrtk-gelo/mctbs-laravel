<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingEdit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'booking_id', 'edited_by', 'old_check_in', 'old_check_out',
        'old_num_guests', 'old_total', 'new_check_in', 'new_check_out',
        'new_num_guests', 'new_total', 'reason', 'additional_downpayment',
    ];

    protected $casts = [
        'old_check_in' => 'date',
        'old_check_out' => 'date',
        'new_check_in' => 'date',
        'new_check_out' => 'date',
        'old_total' => 'decimal:2',
        'new_total' => 'decimal:2',
        'additional_downpayment' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
