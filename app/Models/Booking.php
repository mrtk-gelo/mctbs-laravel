<?php

namespace App\Models;

use App\Support\BookingFinancialContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'user_id',
        'walk_in_phone',
        'walk_in_email',
        'unit_id',
        'check_in',
        'check_out',
        'adult_count',
        'child_count',
        'child_free_through_age',
        'total_price',
        'booking_reference',
        'pricing_mode',
        'nightly_rate',
        'room_subtotal',
        'extra_guest_count',
        'extra_guest_fee',
        'extra_guest_total',
        'status',
        'accepted_at',
        'payment_deadline',
        'feedback_token',
        'feedback_token_expires_at',
        'ticket_email_status',
        'ticket_email_attempted_at',
        'ticket_email_sent_at',
        'checked_in_at',
        'checked_out_at',
        'early_checkout_reason',
        'cancellation_reason',
        'rejection_reason',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'accepted_at' => 'datetime',
        'payment_deadline' => 'datetime',
        'feedback_token_expires_at' => 'datetime',
        'ticket_email_attempted_at' => 'datetime',
        'ticket_email_sent_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'adult_count' => 'integer',
        'child_count' => 'integer',
        'child_free_through_age' => 'integer',
        'total_price' => 'decimal:2',
        'nightly_rate' => 'decimal:2',
        'room_subtotal' => 'decimal:2',
        'extra_guest_count' => 'integer',
        'extra_guest_fee' => 'decimal:2',
        'extra_guest_total' => 'decimal:2',
    ];

    protected $attributes = [
        'pricing_mode' => 'Per Unit',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function displayGuestName(): string
    {
        $userName = trim(($this->guest?->first_name ?? '').' '.($this->guest?->last_name ?? ''));
        if ($userName !== '') {
            return $userName;
        }

        $primaryGuest = $this->relationLoaded('guests')
            ? $this->guests->firstWhere('is_primary_guest', true)
            : $this->guests()->where('is_primary_guest', true)->first();

        return $primaryGuest?->full_name ?? 'Guest';
    }

    public function checkedOutEarly(): bool
    {
        return $this->checked_out_at !== null
            && $this->checked_out_at->toDateString() < $this->check_out->toDateString();
    }

    public function displayGuestEmail(): ?string
    {
        return $this->guest?->email ?? $this->walk_in_email;
    }

    public function displayGuestPhone(): ?string
    {
        return $this->guest?->phone ?? $this->walk_in_phone;
    }

    public function guests(): HasMany
    {
        return $this->hasMany(BookingGuest::class, 'booking_id', 'booking_id');
    }

    public function activeGuests(): HasMany
    {
        return $this->guests()->where('roster_status', BookingGuest::ROSTER_ACTIVE);
    }

    public function effectiveGuests(): HasMany
    {
        return $this->activeGuests()->where(function ($query): void {
            $query->whereNull('attendance_status')
                ->orWhereIn('attendance_status', [
                    BookingGuest::ATTENDANCE_EXPECTED,
                    BookingGuest::ATTENDANCE_ARRIVED,
                ]);
        });
    }

    public function physicalOccupancy(): int
    {
        return $this->activeGuests()
            ->where('attendance_status', BookingGuest::ATTENDANCE_ARRIVED)
            ->count();
    }

    public function guestEvents(): HasMany
    {
        return $this->hasMany(BookingGuestEvent::class, 'booking_id', 'booking_id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(BookingDiscount::class, 'booking_id', 'booking_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }

    public function additionalCharges(): HasMany
    {
        return $this->hasMany(AdditionalCharge::class, 'booking_id', 'booking_id');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(BookingAddon::class, 'booking_id', 'booking_id');
    }

    public function damages(): HasMany
    {
        return $this->hasMany(BookingDamage::class, 'booking_id', 'booking_id');
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'booking_id', 'booking_id');
    }

    public function feedback(): HasMany
    {
        return $this->legacyFeedback();
    }

    public function legacyFeedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'booking_id', 'booking_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'booking_id', 'booking_id');
    }

    public function hasReviewOrLegacyFeedback(): bool
    {
        return $this->review()->exists() || $this->legacyFeedback()->exists();
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'booking_id', 'booking_id');
    }

    public function edits(): HasMany
    {
        return $this->hasMany(BookingEdit::class, 'booking_id', 'booking_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(BookingReceipt::class, 'booking_id', 'booking_id');
    }

    public function amountDueBeforeCheckout(): float
    {
        return BookingFinancialContract::storedAccommodationAmountAfterAppliedDiscounts($this);
    }

    public function downpaymentAmount(): float
    {
        return round($this->amountDueBeforeCheckout() * (float) config('booking.downpayment_fraction'), 2);
    }

    public function amountDueAtCheckout(): float
    {
        return BookingFinancialContract::finalAmountDue($this);
    }

    public function outstandingBalance(): float
    {
        return BookingFinancialContract::outstandingBalance($this);
    }

    public function refundableOverpayment(): float
    {
        return BookingFinancialContract::refundableOverpayment($this);
    }

    public function archive(): HasOne
    {
        return $this->hasOne(TransactionArchive::class, 'booking_id', 'booking_id');
    }
}
