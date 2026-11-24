<?php

namespace App\Models;

use App\Mail\PasswordResetLink;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'profile_image',
        'is_active',
        'deactivated_at',
        'deactivated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'registered_at' => 'datetime',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->registered_at ??= now();
        });
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function transientHouses()
    {
        return $this->hasMany(TransientHouse::class, 'owner_id', 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'user_id', 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }

    public function invitationsSent()
    {
        return $this->hasMany(Invitation::class, 'invited_by', 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function dashboardPath(): string
    {
        return match ($this->role?->name) {
            'Account Admin' => '/accadmin/dashboard',
            'Owner' => '/owner/dashboard',
            'Staff' => '/staff/dashboard',
            default => '/',
        };
    }

    public function initials(): string
    {
        return Str::upper(
            Str::substr(trim($this->first_name), 0, 1)
                .Str::substr(trim($this->last_name), 0, 1)
        );
    }

    /**
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        if (! $this->is_active) {
            return;
        }

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $this->email,
        ]);

        Mail::to($this->email)->send(new PasswordResetLink($this, $resetUrl));
    }
}
