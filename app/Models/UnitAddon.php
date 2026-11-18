<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitAddon extends Model
{
    protected $table = 'unit_addons';

    protected $primaryKey = 'unit_addon_id';

    public $timestamps = false;

    protected $fillable = ['unit_id', 'name', 'price'];

    protected $casts = ['price' => 'decimal:2'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }
}
