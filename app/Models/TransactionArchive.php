<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionArchive extends Model
{
    protected $table = 'transaction_archives';

    protected $primaryKey = 'archive_id';

    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'archived_by',
        'archive_note',
        'archived_at',
        'scheduled_deletion_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'scheduled_deletion_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by', 'user_id');
    }
}
