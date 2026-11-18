<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'booking_id',
        'payment_method',
        'amount',
        'payment_status',
        'payment_date',
        'reference_number',
        'paymongo_payment_id',
        'paymongo_payment_intent_id',
        'paymongo_checkout_session_id',
        'payment_receipt',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            $payment->reference_number_key = self::referenceNumberKey($payment->reference_number);
        });

        static::updating(function (Payment $payment): void {
            if ($payment->isDirty('reference_number')) {
                $payment->reference_number_key = self::referenceNumberKey($payment->reference_number);
            }
        });
    }

    public static function referenceNumberKey(?string $referenceNumber): ?string
    {
        $normalizedReference = mb_strtolower(trim($referenceNumber ?? ''));

        return $normalizedReference === '' ? null : hash('sha256', $normalizedReference);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
