<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'house_id', 'house_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'house_id', 'house_id');
    }
}
