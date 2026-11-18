<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $table = 'reports';

    protected $primaryKey = 'report_id';

    public $timestamps = false;

    protected $fillable = [
        'house_id',
        'generated_by',
        'report_type',
        'date_from',
        'date_to',
        'file_path',
        'created_at',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'created_at' => 'datetime',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(TransientHouse::class, 'house_id', 'house_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }
}
