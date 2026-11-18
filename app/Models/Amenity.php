<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    protected $table = 'amenities';

    protected $primaryKey = 'amenity_id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    public function houses(): BelongsToMany
    {
        return $this->belongsToMany(
            TransientHouse::class,
            'house_amenities',
            'amenity_id',
            'house_id',
            'amenity_id',
            'house_id'
        )->withPivot('house_amenity_id');
    }
}
