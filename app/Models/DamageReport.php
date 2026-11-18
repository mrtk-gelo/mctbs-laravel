<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageReport extends Model
{
    public const APPROVAL_PENDING = 'Pending';

    public const APPROVAL_APPROVED = 'Approved';

    public const APPROVAL_REJECTED = 'Rejected';

    public const APPROVAL_WAIVED = 'Waived';

    public const CHARGE_INCLUDED = 'Included';

    public const APPROVAL_STATUSES = [
        self::APPROVAL_PENDING,
        self::APPROVAL_APPROVED,
        self::APPROVAL_REJECTED,
        self::APPROVAL_WAIVED,
    ];

    protected $table = 'damage_reports';

    protected $primaryKey = 'damage_id';

    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'unit_id',
        'unit_damage_item_id',
        'reported_by',
        'description',
        'amount',
        'quantity',
        'unit_price',
        'status',
        'approval_status',
        'approval_history',
        'reported_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'approval_history' => 'array',
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by', 'user_id');
    }

    public function unitDamageItem(): BelongsTo
    {
        return $this->belongsTo(UnitDamageItem::class, 'unit_damage_item_id', 'unit_damage_item_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by', 'user_id');
    }

    public function calculatedChargeTotal(): float
    {
        if ($this->unit_price !== null) {
            return round((float) $this->unit_price * (int) $this->quantity, 2);
        }

        return round((float) $this->amount, 2);
    }

    public function isIncludedInFinalAmount(): bool
    {
        return in_array($this->approval_status, [self::APPROVAL_APPROVED, self::CHARGE_INCLUDED], true)
            && $this->unit_price !== null
            && $this->quantity > 0;
    }
}
