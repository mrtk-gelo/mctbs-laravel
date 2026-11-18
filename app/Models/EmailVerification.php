<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'token', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
