<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    public function displayName(): string
    {
        return $this->name === 'System Admin' ? 'Account Manager' : $this->name;
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}
