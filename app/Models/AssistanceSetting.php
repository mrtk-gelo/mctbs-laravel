<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistanceSetting extends Model
{
    protected $table = 'assistance_settings';

    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'house_id',
        'updated_by',
        'welcome_message',
        'handoff_message',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(TransientHouse::class, 'house_id', 'house_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }
}
