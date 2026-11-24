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

test('a unit inquiry goes only to the owner of that unit\'s house', function () {
    Mail::fake();

    $ownerRoleId = Role::where('name', 'Owner')->firstOrFail()->role_id;
    $makeOwner = fn (string $email): User => User::create([
        'role_id' => $ownerRoleId,
        'first_name' => 'Owner',
        'last_name' => 'Test',
        'email' => $email,
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $owner = $makeOwner('house-a-owner@example.com');
    $otherOwner = $makeOwner('house-b-owner@example.com');

    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'House A',
        'address' => '1 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5551234',
        'is_active' => true,
    ]);
    $unit = $house->units()->create([
        'unit_type' => 'Family Room',
        'capacity' => 4,
        'price' => '2500.00',
        'status' => 'Available',
    ]);

    $this->post(route('public.units.inquiries.store', $unit), [
        'name' => 'Potential Guest',
        'email' => 'guest@example.com',
        'message' => 'Do you allow pets?',
    ])->assertRedirect()->assertSessionHas('success');

    Mail::assertSent(PropertyInquiry::class, 1);
    Mail::assertSent(PropertyInquiry::class, fn (PropertyInquiry $mail): bool => $mail->hasTo($owner->email)
        && ! $mail->hasTo($otherOwner->email)
        && $mail->unitName === 'Family Room');
});

test('an inquiry shows a friendly error instead of crashing when email delivery fails', function () {
    Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Brevo is down'));

    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Property',
        'last_name' => 'Owner',
        'email' => 'failing-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Failing House',
        'address' => '2 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5551234',
        'is_active' => true,
    ]);

    $this->post(route('public.houses.inquiries.store', $house), [
        'name' => 'Potential Guest',
        'email' => 'guest@example.com',
        'message' => 'Hello?',
    ])->assertSessionHasErrors('inquiry');
});
