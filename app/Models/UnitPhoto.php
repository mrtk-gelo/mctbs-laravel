<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitPhoto extends Model
{
    protected $table = 'unit_photos';

    protected $primaryKey = 'photo_id';

    public $timestamps = false;

    protected $fillable = [
        'unit_id', 'photo_path', 'is_cover', 'sort_order',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }
}
