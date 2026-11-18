<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $primaryKey = 'invitation_id';

    public $timestamps = false;

    protected $fillable = [
        'invited_by',
        'house_id',
        'role_id',
        'email',
        'token',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by', 'user_id');
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(TransientHouse::class, 'house_id');
    }
}
