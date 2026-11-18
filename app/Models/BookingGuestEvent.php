<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingGuestEvent extends Model
{
    public const TYPE_ARRIVAL = 'Arrival';

    public const TYPE_NO_SHOW = 'No-show';

    public const TYPE_REPLACEMENT = 'Replacement';

    public const TYPE_CLASSIFICATION_CHANGE = 'Classification change';

    public const TYPE_IDENTITY_VERIFICATION = 'Identity verification';

    public const TYPE_IDENTITY_EXCEPTION = 'Identity exception';

    public const TYPE_ROSTER_REMOVAL = 'Roster removal';

    public const EVENT_TYPES = [
        self::TYPE_ARRIVAL,
        self::TYPE_NO_SHOW,
        self::TYPE_REPLACEMENT,
        self::TYPE_CLASSIFICATION_CHANGE,
        self::TYPE_IDENTITY_VERIFICATION,
        self::TYPE_IDENTITY_EXCEPTION,
        self::TYPE_ROSTER_REMOVAL,
    ];

    protected $table = 'booking_guest_events';

    protected $primaryKey = 'booking_guest_event_id';

    protected $fillable = [
        'booking_id',
        'booking_guest_id',
        'related_booking_guest_id',
        'event_type',
        'performed_by',
        'reason',
        'event_at',
        'before_state',
        'after_state',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function affectedGuest(): BelongsTo
    {
        return $this->belongsTo(BookingGuest::class, 'booking_guest_id', 'booking_guest_id');
    }

    public function relatedGuest(): BelongsTo
    {
        return $this->belongsTo(BookingGuest::class, 'related_booking_guest_id', 'booking_guest_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by', 'user_id');
    }
}
