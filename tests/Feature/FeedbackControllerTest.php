<?php

use App\Models\Booking;
use App\Models\Review;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use App\Support\BookingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeFeedbackScenario(bool $walkIn = false): array
{
    static $scenarioNumber = 0;
    $scenarioNumber++;

    $owner = User::create([
        'role_id' => Role::query()->where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'House',
        'last_name' => 'Owner',
        'email' => 'feedback-owner-'.$scenarioNumber.'@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Quiet Mountain House',
        'address' => '10 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5552000',
        'is_active' => true,
    ]);
    $unit = Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => 'Family Room',
        'capacity' => 4,
        'price' => 1800,
        'status' => 'Available',
    ]);
    $guest = $walkIn ? null : User::create([
        'role_id' => Role::query()->where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Feedback',
        'last_name' => 'Guest',
        'email' => 'feedback-guest@example.com',
        'password' => 'GuestPass123!',
        'is_active' => true,
    ]);
    $token = 'feedback-token-for-tests-'.$scenarioNumber;
    $booking = Booking::create([
        'user_id' => $guest?->user_id,
        'walk_in_email' => $walkIn ? 'walk-in@example.com' : null,
        'unit_id' => $unit->unit_id,
        'check_in' => now()->subDays(2),
        'check_out' => now()->subDay(),
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'booking_reference' => 'MCTBS-'.str_pad((string) (100 + $scenarioNumber), 6, '0', STR_PAD_LEFT),
        'status' => BookingStatus::CHECKED_IN,
        'feedback_token' => hash('sha256', $token),
        'feedback_token_expires_at' => now()->addDays(7),
    ]);

    return compact('owner', 'house', 'unit', 'guest', 'booking', 'token');
}

it('accepts a valid rating and comment once and displays a professional success state', function () {
    ['guest' => $guest, 'booking' => $booking, 'token' => $token] = makeFeedbackScenario();

    $this->actingAs($guest)
        ->get(route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => $token]))
        ->assertSee('Rate your stay')
        ->assertSee('Share what went well')
        ->assertSee('Submit feedback')
        ->assertSee('feedback-rating-option', false);

    $this->post(route('guest.feedback.store', ['booking' => $booking->booking_id, 'token' => $token]), [
        'rating' => 5,
        'comment' => 'Lovely <b>stay</b>.',
    ])->assertRedirect(route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => $token]))
        ->assertSessionHas('success', 'Thank you for sharing your feedback.');

    expect(Review::query()->where('booking_id', $booking->booking_id)->value('rating'))->toBe(5);

    $this->get(route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => $token]))
        ->assertSee('Thank you for sharing your feedback.')
        ->assertSee('5 / 5')
        ->assertSee('Lovely &lt;b&gt;stay&lt;/b&gt;', false)
        ->assertDontSee('<b>stay</b>', false);

    $this->get(route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => $token]))
        ->assertSee('already been used')
        ->assertDontSee('name="rating"', false);
});

it('rejects expired links and avoids revealing walk-in booking details for invalid links', function () {
    ['guest' => $guest, 'booking' => $booking, 'token' => $token] = makeFeedbackScenario();
    $booking->update(['feedback_token_expires_at' => now()->subMinute()]);

    $this->actingAs($guest)
        ->get(route('guest.feedback.show', ['booking' => $booking->booking_id, 'token' => $token]))
        ->assertSee('This feedback link has expired.')
        ->assertDontSee('name="rating"', false);

    $this->post(route('guest.feedback.store', ['booking' => $booking->booking_id, 'token' => $token]), [
        'rating' => 4,
        'comment' => 'Too late.',
    ])->assertSessionHasErrors('feedback');
    expect(Review::query()->where('booking_id', $booking->booking_id)->exists())->toBeFalse();

    ['house' => $walkInHouse, 'booking' => $walkInBooking] = makeFeedbackScenario(walkIn: true);
    $this->get(route('guest.feedback.show', [
        'booking' => $walkInBooking->booking_id,
        'token' => 'incorrect-token',
    ]))->assertSee('This feedback link is invalid or has expired.')
        ->assertDontSee($walkInHouse->name);
});

it('does not disclose a used review for an invalid feedback token', function () {
    ['guest' => $guest, 'unit' => $unit, 'booking' => $booking, 'token' => $token] = makeFeedbackScenario();
    Review::create([
        'booking_id' => $booking->booking_id,
        'unit_id' => $unit->unit_id,
        'user_id' => $guest->user_id,
        'rating' => 4,
        'comment' => 'Already submitted.',
    ]);

    $this->actingAs($guest)
        ->get(route('guest.feedback.show', [
            'booking' => $booking->booking_id,
            'token' => 'not-the-token',
        ]))->assertSee('This feedback link is invalid or has expired.')
        ->assertDontSee('already been used');
});
