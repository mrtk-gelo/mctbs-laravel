<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $table = 'units';

    protected $primaryKey = 'unit_id';

    public $timestamps = false;

    protected $fillable = [
        'house_id',
        'unit_type',
        'capacity',
        'maximum_capacity',
        'check_in_time',
        'check_out_time',
        'pricing_mode',
        'extra_guest_fee',
        'child_free_through_age',
        'price',
        'status',
        'description',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'maximum_capacity' => 'integer',
        'extra_guest_fee' => 'decimal:2',
        'child_free_through_age' => 'integer',
        'price' => 'decimal:2',
        'archived_at' => 'datetime',
        'archived_with_house' => 'boolean',
    ];

    protected $attributes = [
        'check_in_time' => '14:00:00',
        'check_out_time' => '12:00:00',
        'pricing_mode' => 'Per Unit',
    ];

    public function house()
    {
        return $this->belongsTo(TransientHouse::class, 'house_id', 'house_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'unit_id', 'unit_id');
    }

    public function availability()
    {
        return $this->hasMany(UnitCalendar::class, 'unit_id', 'unit_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(UnitPhoto::class, 'unit_id', 'unit_id')
            ->orderBy('sort_order')
            ->orderBy('photo_id');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(UnitAddon::class, 'unit_id', 'unit_id');
    }

    public function damageItems(): HasMany
    {
        return $this->hasMany(UnitDamageItem::class, 'unit_id', 'unit_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'unit_id', 'unit_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'unit_id', 'unit_id');
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'unit_id', 'unit_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by', 'user_id');
    }
}
