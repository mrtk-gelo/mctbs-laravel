<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingEdit extends Model
{
    protected $table = 'booking_edits';

    protected $primaryKey = 'edit_id';

    protected $fillable = [
        'booking_id',
        'edited_by',
        'old_check_in',
        'old_check_out',
        'new_check_in',
        'new_check_out',
        'old_total_price',
        'new_total_price',
        'edit_reason',
    ];

    protected $casts = [
        'old_check_in' => 'datetime',
        'old_check_out' => 'datetime',
        'new_check_in' => 'datetime',
        'new_check_out' => 'datetime',
        'old_total_price' => 'decimal:2',
        'new_total_price' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'user_id');
    }
}
