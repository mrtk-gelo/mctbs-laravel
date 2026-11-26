<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatQuickReply extends Model
{
    protected $table = 'chat_quick_replies';

    protected $primaryKey = 'quick_reply_id';

    protected $fillable = [
        'house_id',
        'created_by',
        'title',
        'content',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(TransientHouse::class, 'house_id', 'house_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
