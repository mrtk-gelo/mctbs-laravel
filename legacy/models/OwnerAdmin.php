<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerAdmin extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'owner_id', 'admin_id', 'house_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function house()
    {
        return $this->belongsTo(TransientHouse::class, 'house_id');
    }
}
