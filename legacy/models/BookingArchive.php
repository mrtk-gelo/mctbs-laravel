<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingArchive extends Model
{
    // Laravel would guess 'booking_archives' — actual table is 'bookings_archive'
    protected $table = 'bookings_archive';

    protected $fillable = [
        'booking_code', 'unit_id', 'guest_id', 'check_in', 'check_out',
        'num_guests', 'extra_guest_charge', 'total_nights', 'price_per_night',
        'total_amount', 'downpayment_amount', 'remaining_balance', 'status',
        'payment_status', 'cancellation_policy_acknowledged', 'guest_notes',
        'admin_notes', 'archive_note', 'rejection_reason', 'cancellation_reason',
        'cancelled_by', 'cancelled_by_role', 'refund_status', 'refund_details',
        'payment_deadline', 'confirmed_at', 'completed_at', 'archived_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'cancellation_policy_acknowledged' => 'boolean',
        'payment_deadline' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    // No belongsTo() relationships on purpose: this table has no foreign
    // key constraints, so the original unit/guest may no longer exist.
}
