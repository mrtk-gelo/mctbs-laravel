<?php

use App\Mail\PropertyInquiry;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('a transient inquiry is emailed directly to the property owner', function () {
    Mail::fake();

    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Property',
        'last_name' => 'Owner',
        'email' => 'property-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Inquiry House',
        'address' => '10 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5551234',
        'is_active' => true,
    ]);

    $this->post(route('public.houses.inquiries.store', $house), [
        'name' => 'Potential Guest',
        'email' => 'guest@example.com',
        'message' => 'Is the property available next month?',
    ])->assertRedirect()
        ->assertSessionHas('success', 'Your inquiry has been sent to the property owner.');

    Mail::assertSent(PropertyInquiry::class, function (PropertyInquiry $mail) use ($owner): bool {
        return $mail->hasTo($owner->email)
            && $mail->senderEmail === 'guest@example.com'
            && $mail->senderName === 'Potential Guest'
            && $mail->propertyName === 'Inquiry House'
            && $mail->inquiryMessage === 'Is the property available next month?'
            && $mail->envelope()->replyTo[0]->address === 'guest@example.com';
    });
});
