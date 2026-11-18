<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransientUnit extends Model
{
    protected $fillable = [
        'house_id', 'name', 'description', 'max_guests',
        'price_per_night', 'amenities', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_per_night' => 'decimal:2',
        'amenities' => 'array',
    ];

    public function house()
    {
        return $this->belongsTo(TransientHouse::class, 'house_id');
    }

    public function photos()
    {
        return $this->hasMany(UnitPhoto::class, 'unit_id');
    }

    public function calendarEntries()
    {
        return $this->hasMany(UnitCalendar::class, 'unit_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'unit_id');
    }

    public function queueEntries()
    {
        return $this->hasMany(BookingQueue::class, 'unit_id');
    }
}
