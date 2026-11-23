<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function show(Request $request, Booking $booking, string $token): View|RedirectResponse
    {
        if ($booking->user_id !== null) {
            if (! $request->user()) {
                return redirect()->guest(route('login'));
            }

            abort_unless($request->user()->role?->name === 'Guest', 403, 'Only the guest can submit feedback.');
            abort_unless($request->user()->user_id === $booking->user_id, 403, 'This feedback link belongs to another guest.');
        }

        if ($booking->user_id === null && ! $this->hasValidToken($booking, $token)) {
            return view('guest.feedback.invalid', [
                'message' => $this->feedbackMessageForPublicLink($booking, $token),
            ]);
        }

        $booking->load(['unit.house', 'guest', 'review', 'guests']);

        return view('guest.feedback.show', [
            'booking' => $booking,
            'token' => $token,
            'feedbackMessage' => $this->feedbackMessage($booking, $token),
        ]);
    }

    public function store(Request $request, Booking $booking, string $token): RedirectResponse
    {
        if ($booking->user_id !== null) {
            abort_unless($request->user()?->role?->name === 'Guest', 403, 'Only the guest can submit feedback.');
            abort_unless($request->user()->user_id === $booking->user_id, 403, 'This feedback link belongs to another guest.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($booking, $token, $validated): Review {
            $lockedBooking = Booking::query()
                ->with('unit.house')
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->hasValidToken($lockedBooking, $token)) {
                throw ValidationException::withMessages([
                    'feedback' => $this->feedbackMessage($lockedBooking, $token),
                ]);
            }

            if ($lockedBooking->hasReviewOrLegacyFeedback()) {
                throw ValidationException::withMessages([
                    'feedback' => 'This feedback link has already been used.',
                ]);
            }

            $review = Review::create([
                'booking_id' => $lockedBooking->booking_id,
                'unit_id' => $lockedBooking->unit_id,
                'user_id' => $lockedBooking->user_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $this->notifyHouse($lockedBooking, $review);

            return $review;
        });

        return redirect()
            ->route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => $token])
            ->with('success', 'Thank you for sharing your feedback.');
    }

    private function feedbackMessage(Booking $booking, string $token): ?string
    {
        if (! hash_equals((string) $booking->feedback_token, hash('sha256', $token))) {
            return 'This feedback link is invalid or has expired.';
        }

        if ($booking->hasReviewOrLegacyFeedback()) {
            return 'This feedback link has already been used. Thank you for sharing your feedback.';
        }

        if (! $booking->feedback_token_expires_at?->isFuture()) {
            return 'This feedback link has expired.';
        }

        return null;
    }

    private function hasValidToken(Booking $booking, string $token): bool
    {
        return $booking->feedback_token !== null
            && hash_equals($booking->feedback_token, hash('sha256', $token))
            && $booking->feedback_token_expires_at?->isFuture() === true;
    }

    private function feedbackMessageForPublicLink(Booking $booking, string $token): string
    {
        $tokenMatches = $booking->feedback_token !== null
            && hash_equals($booking->feedback_token, hash('sha256', $token));

        if (! $tokenMatches) {
            return 'This feedback link is invalid or has expired.';
        }

        if ($booking->hasReviewOrLegacyFeedback()) {
            return 'This feedback link has already been used. Thank you for sharing your feedback.';
        }

        return 'This feedback link has expired.';
    }

    private function notifyHouse(Booking $booking, Review $review): void
    {
        $house = $booking->unit->house;
        $staffRoleId = Role::query()->where('name', 'Staff')->value('role_id');
        $staffEmails = Invitation::query()
            ->where('house_id', $house->house_id)
            ->where('role_id', $staffRoleId)
            ->whereNotNull('used_at')
            ->pluck('email');
        $recipientIds = collect([$house->owner_id])
            ->merge(User::query()->whereIn('email', $staffEmails)->pluck('user_id'))
            ->unique();

        foreach ($recipientIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'booking_id' => $booking->booking_id,
                'type' => 'review_submitted',
                'title' => 'New guest review',
                'message' => "A guest submitted a {$review->rating}-star review for {$booking->unit->unit_type}.",
                'is_read' => false,
                'created_at' => now(),
            ]);
        }
    }
}
