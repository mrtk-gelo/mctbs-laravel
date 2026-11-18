<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitCalendar extends Model
{
    protected $table = 'unit_availability';

    protected $primaryKey = 'availability_id';

    public $timestamps = false;

    protected $fillable = [
        'unit_id', 'date', 'status', 'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }
}
