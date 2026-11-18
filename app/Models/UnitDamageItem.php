<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitDamageItem extends Model
{
    protected $table = 'unit_damage_items';

    protected $primaryKey = 'unit_damage_item_id';

    public $timestamps = false;

    protected $fillable = ['unit_id', 'name', 'price'];

    protected $casts = ['price' => 'decimal:2'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }
}
