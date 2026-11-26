<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransientHouse extends Model
{
    protected $table = 'transient_houses';

    protected $primaryKey = 'house_id';

    public $timestamps = false;

    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'description',
        'city',
        'barangay',
        'contact_number',
        'policies',
        'social_links',
        'cover_photo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'social_links' => 'array',
        'archived_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'house_id', 'house_id');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(
            Amenity::class,
            'house_amenities',
            'house_id',
            'amenity_id',
            'house_id',
            'amenity_id'
        )->withPivot('house_amenity_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'house_id', 'house_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'house_id', 'house_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by', 'user_id');
    }
}
