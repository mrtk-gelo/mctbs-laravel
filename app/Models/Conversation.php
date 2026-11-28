<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $table = 'conversations';

    protected $primaryKey = 'conversation_id';

    protected $fillable = [
        'house_id',
        'unit_id',
        'guest_user_id',
        'assigned_to',
        'status',
        'subject',
        'last_message_at',
        'resolved_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(TransientHouse::class, 'house_id', 'house_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id', 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id', 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'conversation_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ConversationNote::class, 'conversation_id', 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id', 'conversation_id')->latestOfMany('sent_at');
    }

    public function latestHumanMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id', 'conversation_id')
            ->where('sender_type', 'user')
            ->latestOfMany('sent_at');
    }
}
