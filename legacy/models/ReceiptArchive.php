<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptArchive extends Model
{
    // Laravel would guess 'receipt_archives' — actual table is 'receipts_archive'
    protected $table = 'receipts_archive';

    public $timestamps = false;

    protected $fillable = [
        'booking_id', 'receipt_number', 'generated_at', 'archived_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'archived_at' => 'datetime',
    ];
}
