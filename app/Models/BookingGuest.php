<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingGuest extends Model
{
    public const ROSTER_ACTIVE = 'Active';

    public const ROSTER_REMOVED = 'Removed';

    public const ROSTER_STATUSES = [
        self::ROSTER_ACTIVE,
        self::ROSTER_REMOVED,
    ];

    public const ATTENDANCE_EXPECTED = 'Expected';

    public const ATTENDANCE_ARRIVED = 'Arrived';

    public const ATTENDANCE_NO_SHOW = 'No-show';

    public const ATTENDANCE_REPLACED = 'Replaced';

    public const ATTENDANCE_STATUSES = [
        self::ATTENDANCE_EXPECTED,
        self::ATTENDANCE_ARRIVED,
        self::ATTENDANCE_NO_SHOW,
        self::ATTENDANCE_REPLACED,
    ];

    public const IDENTITY_UNVERIFIED = 'Unverified';

    public const IDENTITY_VERIFIED = 'Verified';

    public const IDENTITY_EXCEPTION = 'Exception';

    public const IDENTITY_FAILED = 'Failed';

    public const IDENTITY_VERIFICATION_STATUSES = [
        self::IDENTITY_UNVERIFIED,
        self::IDENTITY_VERIFIED,
        self::IDENTITY_EXCEPTION,
        self::IDENTITY_FAILED,
    ];

    protected $table = 'booking_guests';

    protected $primaryKey = 'booking_guest_id';

    protected $fillable = [
        'booking_id',
        'user_id',
        'full_name',
        'guest_type',
        'age',
        'is_free_child',
        'share_amount',
        'is_primary_guest',
        'roster_status',
        'removed_at',
        'removed_by',
        'removal_reason',
        'attendance_status',
        'replaces_booking_guest_id',
        'identity_verification_status',
        'identity_verified_by',
        'identity_verified_at',
        'identity_exception_reason',
        'classification_overridden',
        'classification_override_reason',
        'classification_overridden_by',
        'classification_overridden_at',
    ];

    protected $casts = [
        'share_amount' => 'decimal:2',
        'age' => 'integer',
        'is_free_child' => 'boolean',
        'is_primary_guest' => 'boolean',
        'removed_at' => 'datetime',
        'identity_verified_at' => 'datetime',
        'classification_overridden' => 'boolean',
        'classification_overridden_at' => 'datetime',
    ];

    protected $attributes = [
        'roster_status' => self::ROSTER_ACTIVE,
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function removedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by', 'user_id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(BookingDiscount::class, 'booking_guest_id', 'booking_guest_id');
    }

    public function originalGuest(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_booking_guest_id', 'booking_guest_id');
    }

    public function replacements(): HasMany
    {
        return $this->hasMany(self::class, 'replaces_booking_guest_id', 'booking_guest_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(BookingGuestEvent::class, 'booking_guest_id', 'booking_guest_id');
    }

    public function relatedEvents(): HasMany
    {
        return $this->hasMany(BookingGuestEvent::class, 'related_booking_guest_id', 'booking_guest_id');
    }

    public function identityVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'identity_verified_by', 'user_id');
    }

    public function classificationOverriddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'classification_overridden_by', 'user_id');
    }

    public function hasCheckInIdentityApproval(): bool
    {
        if (! in_array($this->identity_verification_status, [
            self::IDENTITY_VERIFIED,
            self::IDENTITY_EXCEPTION,
        ], true) || $this->identity_verified_by === null || $this->identity_verified_at === null) {
            return false;
        }

        return $this->identity_verification_status !== self::IDENTITY_EXCEPTION
            || trim((string) $this->identity_exception_reason) !== '';
    }
}
