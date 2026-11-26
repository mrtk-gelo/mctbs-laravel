<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatFaq extends Model
{
    protected $table = 'chat_faqs';

    protected $primaryKey = 'faq_id';

    protected $fillable = [
        'house_id',
        'created_by',
        'category',
        'question',
        'answer',
        'keywords',
        'related_url',
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
