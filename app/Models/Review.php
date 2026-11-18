<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Review extends Model
{
    protected $table = 'reviews';

    protected $primaryKey = 'review_id';

    protected $fillable = ['booking_id', 'unit_id', 'user_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    protected static function booted(): void
    {
        static::saving(function (Review $review): void {
            $rating = filter_var($review->getAttributes()['rating'] ?? null, FILTER_VALIDATE_INT);

            if ($rating === false || $rating < 1 || $rating > 5) {
                throw ValidationException::withMessages([
                    'rating' => 'The rating must be between 1 and 5.',
                ]);
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function displayGuestName(): string
    {
        $userName = trim(($this->guest?->first_name ?? '').' '.($this->guest?->last_name ?? ''));

        return $userName !== '' ? $userName : ($this->booking?->displayGuestName() ?? 'Guest');
    }
}
