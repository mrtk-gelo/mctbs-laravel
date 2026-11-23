<?php

use App\Mail\AccountInvitation;
use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sysadmin = User::create([
        'role_id' => Role::where('name', 'System Admin')->firstOrFail()->role_id,
        'first_name' => 'System',
        'last_name' => 'Admin',
        'email' => 'sysadmin@example.com',
        'password' => 'SysAdmin123!',
        'is_active' => true,
    ]);
});

test('a sysadmin invitation creates an owner account and consumes the invitation', function () {
    Mail::fake();

    $inviteResponse = $this->actingAs($this->sysadmin)
        ->post(route('sysadmin.owners.invite'), ['email' => 'invited-owner@example.com'])
        ->assertRedirect(route('sysadmin.owners.index'))
        ->assertSessionHas('success', 'Invitation email sent to invited-owner@example.com.')
        ->assertSessionMissing('invite_link');

    $invitation = Invitation::where('email', 'invited-owner@example.com')->firstOrFail();
    expect($invitation->role->name)->toBe('Owner')
        ->and($invitation->house_id)->toBeNull()
        ->and($invitation->expires_at->isFuture())->toBeTrue();
    Mail::assertSent(AccountInvitation::class, fn (AccountInvitation $mail): bool => $mail->hasTo($invitation->email)
        && str_contains($mail->render(), route('register', ['token' => $invitation->token]))
        && str_contains($mail->render(), 'Owner'));

    $this->actingAs($this->sysadmin)
        ->post(route('sysadmin.owners.invite'), ['email' => 'invited-owner@example.com'])
        ->assertSessionHas('success', 'Invitation email sent to invited-owner@example.com.');
    expect(Invitation::where('email', 'invited-owner@example.com')->whereNull('used_at')->count())->toBe(1);
    Mail::assertSent(AccountInvitation::class, 2);

    $this->get(route('register', ['token' => $invitation->token]))
        ->assertOk()
        ->assertSee('value="invited-owner@example.com"', false)
        ->assertSee('readonly', false);

    $this->post(route('register.store'), [
        'token' => $invitation->token,
        'first_name' => 'Invited',
        'last_name' => 'Owner',
        'email' => 'attacker@example.com',
        'phone' => '5551234567',
        'password' => 'OwnerPass123!',
        'password_confirmation' => 'OwnerPass123!',
        'terms_accepted' => '1',
    ])->assertRedirect('/owner/dashboard');

    $owner = User::where('email', 'invited-owner@example.com')->firstOrFail();
    expect($owner->role->name)->toBe('Owner')
        ->and($owner->phone)->toBe('5551234567')
        ->and($owner->email_verified_at)->not->toBeNull()
        ->and($owner->profile_image)->toBeNull()
        ->and($owner->is_active)->toBeTrue()
        ->and($owner->registered_at)->not->toBeNull()
        ->and(Hash::check('OwnerPass123!', $owner->password))->toBeTrue();

    $this->assertAuthenticatedAs($owner);
    $this->assertNotNull($invitation->fresh()->used_at);
    $this->get('/owner/dashboard')->assertOk();

    $this->actingAs($this->sysadmin)
        ->get(route('sysadmin.owners.index'))
        ->assertOk()
        ->assertSee('Owner Management')
        ->assertSee('Invited Owner')
        ->assertSee('invited-owner@example.com')
        ->assertSee('Date Registered')
        ->assertSee('Deactivate selected')
        ->assertSee('Select Invited Owner')
        ->assertDontSee('Active Staff');

    $this->get(route('sysadmin.dashboard'))
        ->assertOk()
        ->assertSee('Manage Owners')
        ->assertDontSee('Active properties')
        ->assertDontSee('Revenue');
});

test('a failed owner invitation email keeps the invitation available for a safe retry', function () {
    config([
        'mail.default' => 'brevo',
        'services.brevo.key' => 'test-brevo-key',
    ]);

    $attempt = 0;
    Http::preventStrayRequests();
    Http::fake([
        'https://api.brevo.com/v3/smtp/email' => function () use (&$attempt) {
            $attempt++;

            return Http::response([], $attempt === 1 ? 500 : 201);
        },
    ]);

    $this->actingAs($this->sysadmin)
        ->post(route('sysadmin.owners.invite'), ['email' => 'retry-owner@example.com'])
        ->assertRedirect(route('sysadmin.owners.index'))
        ->assertSessionHas('error')
        ->assertSessionMissing('success')
        ->assertSessionMissing('invite_link');

    $invitation = Invitation::where('email', 'retry-owner@example.com')->firstOrFail();
    expect($invitation->used_at)->toBeNull();

    $this->post(route('sysadmin.owners.invitations.resend', $invitation))
        ->assertRedirect(route('sysadmin.owners.index'))
        ->assertSessionHas('success', 'Invitation email resent to retry-owner@example.com.');

    expect(Invitation::where('email', 'retry-owner@example.com')->count())->toBe(1)
        ->and($attempt)->toBe(2);
});

test('expired invitations notify the inviter to resend them without duplicate reminders', function () {
    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Resend',
        'last_name' => 'Owner',
        'email' => 'resend-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Owner House',
        'address' => '7 Reminder Road',
        'city' => 'Baguio',
        'contact_number' => '5552001',
        'is_active' => true,
    ]);
    $ownerInvitation = Invitation::create([
        'invited_by' => $this->sysadmin->user_id,
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'email' => 'owner-expired@example.com',
        'token' => 'expired-owner-invite-token',
        'expires_at' => now()->subHour(),
    ]);
    $staffInvitation = Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'email' => 'staff-expired@example.com',
        'token' => 'expired-staff-invite-token',
        'expires_at' => now()->subHour(),
    ]);

    $this->artisan('invitation:remind-expired')->assertSuccessful();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $this->sysadmin->user_id,
        'type' => 'invitation_expired',
        'title' => 'Invitation expired',
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $owner->user_id,
        'type' => 'invitation_expired',
        'title' => 'Invitation expired',
    ]);
    expect($ownerInvitation->fresh()->reminder_sent_at)->not->toBeNull()
        ->and($staffInvitation->fresh()->reminder_sent_at)->not->toBeNull();

    $this->actingAs($this->sysadmin)
        ->get(route('notifications.open', Notification::where('user_id', $this->sysadmin->user_id)->where('type', 'invitation_expired')->firstOrFail()->notification_id))
        ->assertRedirect(route('sysadmin.owners.index'));
    $this->actingAs($owner)
        ->get(route('notifications.open', Notification::where('user_id', $owner->user_id)->where('type', 'invitation_expired')->firstOrFail()->notification_id))
        ->assertRedirect(route('owner.admins.index'));

    $countsBefore = Notification::where('type', 'invitation_expired')->count();
    $this->artisan('invitation:remind-expired')->assertSuccessful();
    expect(Notification::where('type', 'invitation_expired')->count())->toBe($countsBefore);
});

test('sysadmin owner search returns matching records over ajax', function () {
    $ownerRole = Role::where('name', 'Owner')->firstOrFail();
    User::create([
        'role_id' => $ownerRole->role_id,
        'first_name' => 'Harbor',
        'last_name' => 'Owner',
        'email' => 'harbor-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    User::create([
        'role_id' => $ownerRole->role_id,
        'first_name' => 'Forest',
        'last_name' => 'Owner',
        'email' => 'forest-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($this->sysadmin)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get(route('sysadmin.owners.index', ['q' => 'Harbor']))
        ->assertOk()
        ->assertSee('Harbor Owner')
        ->assertSee('Deactivate')
        ->assertDontSee('Forest Owner')
        ->assertDontSee('<x-shell', false);
});

test('a sysadmin can delete a single owner account with no protected records', function () {
    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Ready',
        'last_name' => 'Owner',
        'email' => 'ready-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => false,
    ]);

    $this->actingAs($this->sysadmin)
        ->delete(route('sysadmin.owners.destroy', $owner->user_id))
        ->assertRedirect(route('sysadmin.owners.deactivated'))
        ->assertSessionHas('success', 'Owner account for Ready Owner was deleted.');

    $this->assertDatabaseMissing('users', ['user_id' => $owner->user_id]);
    $this->assertDatabaseHas('users', ['user_id' => $this->sysadmin->user_id]);
});

test('a sysadmin cannot delete an owner account linked to a property', function () {
    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Property',
        'last_name' => 'Owner',
        'email' => 'property-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => false,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Protected House',
        'address' => '1 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5550003',
        'is_active' => true,
    ]);

    $this->actingAs($this->sysadmin)
        ->delete(route('sysadmin.owners.destroy', $owner->user_id))
        ->assertRedirect(route('sysadmin.owners.deactivated'))
        ->assertSessionHas('error', 'Property Owner cannot be deleted while linked to properties. Reassign or remove those records first.');

    $this->assertDatabaseHas('users', ['user_id' => $owner->user_id]);
    $this->assertDatabaseHas('transient_houses', ['house_id' => $house->house_id]);
});

test('a sysadmin can bulk delete multiple unreferenced owner accounts', function () {
    $ownerRoleId = Role::where('name', 'Owner')->firstOrFail()->role_id;
    $owners = collect(['one-owner@example.com', 'two-owner@example.com'])
        ->map(fn (string $email): User => User::create([
            'role_id' => $ownerRoleId,
            'first_name' => 'Bulk',
            'last_name' => 'Owner',
            'email' => $email,
            'password' => 'OwnerPass123!',
            'is_active' => false,
        ]));

    $this->actingAs($this->sysadmin)
        ->delete(route('sysadmin.owners.bulk-destroy'), [
            'owner_ids' => $owners->pluck('user_id')->all(),
        ])
        ->assertRedirect(route('sysadmin.owners.deactivated'))
        ->assertSessionHas('success', '2 owner account(s) deleted.');

    foreach ($owners as $owner) {
        $this->assertDatabaseMissing('users', ['user_id' => $owner->user_id]);
    }
});

test('bulk owner deletion deletes none when any selected account has protected records', function () {
    $ownerRoleId = Role::where('name', 'Owner')->firstOrFail()->role_id;
    $deletableOwner = User::create([
        'role_id' => $ownerRoleId,
        'first_name' => 'Safe',
        'last_name' => 'Owner',
        'email' => 'safe-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => false,
    ]);
    $protectedOwner = User::create([
        'role_id' => $ownerRoleId,
        'first_name' => 'Protected',
        'last_name' => 'Owner',
        'email' => 'protected-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => false,
    ]);
    $house = TransientHouse::create([
        'owner_id' => $protectedOwner->user_id,
        'name' => 'Protected House',
        'address' => '2 Test Road',
        'city' => 'Baguio',
        'contact_number' => '5550004',
        'is_active' => true,
    ]);

    $this->actingAs($this->sysadmin)
        ->delete(route('sysadmin.owners.bulk-destroy'), [
            'owner_ids' => [$deletableOwner->user_id, $protectedOwner->user_id],
        ])
        ->assertRedirect(route('sysadmin.owners.deactivated'))
        ->assertSessionHas('error', 'No accounts were deleted. Protected Owner cannot be deleted while linked to properties. Reassign or remove those records first.');

    $this->assertDatabaseHas('users', ['user_id' => $deletableOwner->user_id]);
    $this->assertDatabaseHas('users', ['user_id' => $protectedOwner->user_id]);
    $this->assertDatabaseHas('transient_houses', ['house_id' => $house->house_id]);
});

test('bulk owner deletion rejects empty and non-owner selections', function () {
    $this->actingAs($this->sysadmin)
        ->delete(route('sysadmin.owners.bulk-destroy'), [])
        ->assertSessionHasErrors('owner_ids');

    $this->delete(route('sysadmin.owners.bulk-destroy'), [
        'owner_ids' => [$this->sysadmin->user_id],
    ])->assertSessionHasErrors('owner_ids.0');

    $this->assertDatabaseHas('users', ['user_id' => $this->sysadmin->user_id]);
});

test('non-admin users cannot delete owner accounts', function () {
    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'Protected',
        'last_name' => 'Owner',
        'email' => 'unauthorized-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->delete(route('sysadmin.owners.destroy', $owner->user_id))
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['user_id' => $owner->user_id]);
});

test('expired and used invitations cannot register an account', function () {
    $ownerRoleId = Role::where('name', 'Owner')->firstOrFail()->role_id;

    $expiredInvitation = Invitation::create([
        'invited_by' => $this->sysadmin->user_id,
        'house_id' => null,
        'role_id' => $ownerRoleId,
        'email' => 'expired-owner@example.com',
        'token' => 'expired-token',
        'expires_at' => now()->subMinute(),
    ]);

    $usedInvitation = Invitation::create([
        'invited_by' => $this->sysadmin->user_id,
        'house_id' => null,
        'role_id' => $ownerRoleId,
        'email' => 'used-owner@example.com',
        'token' => 'used-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    foreach ([$expiredInvitation, $usedInvitation] as $invitation) {
        $this->post(route('register.store'), [
            'token' => $invitation->token,
            'first_name' => 'Invited',
            'last_name' => 'Owner',
            'password' => 'OwnerPass123!',
            'password_confirmation' => 'OwnerPass123!',
            'terms_accepted' => '1',
        ])->assertSessionHasErrors('token');
    }

    $this->assertDatabaseMissing('users', ['email' => 'expired-owner@example.com']);
    $this->assertDatabaseMissing('users', ['email' => 'used-owner@example.com']);
    expect($usedInvitation->fresh()->used_at)->not->toBeNull();
});

test('an owner emails a staff invitation with the assigned house and the staff member lands on their dashboard', function () {
    Mail::fake();

    $owner = User::create([
        'role_id' => Role::where('name', 'Owner')->firstOrFail()->role_id,
        'first_name' => 'House',
        'last_name' => 'Owner',
        'email' => 'house-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);

    $house = TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => 'Seaside House',
        'address' => '1 Shoreline Road',
        'city' => 'Cebu City',
        'contact_number' => '5550001',
        'is_active' => true,
    ]);

    $inviteResponse = $this->actingAs($owner)
        ->post(route('owner.admins.invite'), [
            'email' => 'house-staff@example.com',
            'house_id' => $house->house_id,
        ])
        ->assertRedirect(route('owner.admins.index'))
        ->assertSessionHas('success', 'Invitation email sent to house-staff@example.com.')
        ->assertSessionMissing('invite_link');

    $invitation = Invitation::where('email', 'house-staff@example.com')->firstOrFail();
    expect($invitation->role->name)->toBe('Staff')
        ->and($invitation->house_id)->toBe($house->house_id)
        ->and($invitation->expires_at->isFuture())->toBeTrue();
    Mail::assertSent(AccountInvitation::class, fn (AccountInvitation $mail): bool => $mail->hasTo($invitation->email)
        && str_contains($mail->render(), route('register', ['token' => $invitation->token]))
        && str_contains($mail->render(), 'Staff')
        && str_contains($mail->render(), 'Seaside House'));

    $this->get(route('register', ['token' => $invitation->token]))
        ->assertOk()
        ->assertSee('value="house-staff@example.com"', false);

    $this->post(route('register.store'), [
        'token' => $invitation->token,
        'first_name' => 'House',
        'last_name' => 'Staff',
        'email' => 'changed-email@example.com',
        'phone' => '5551234567',
        'password' => 'StaffPass123!',
        'password_confirmation' => 'StaffPass123!',
        'terms_accepted' => '1',
    ])->assertRedirect('/staff/dashboard');

    $staff = User::where('email', 'house-staff@example.com')->firstOrFail();
    expect($staff->role->name)->toBe('Staff')
        ->and($invitation->fresh()->used_at)->not->toBeNull();
    $this->assertAuthenticatedAs($staff);
    $this->get('/staff/dashboard')->assertOk()->assertSee('Seaside House');

    $this->actingAs($owner)
        ->get(route('owner.admins.index'))
        ->assertOk()
        ->assertSee('House Staff')
        ->assertSee('house-staff@example.com')
        ->assertSee('Seaside House')
        ->assertDontSee('Pending invitations')
        ->assertDontSee('Properties available to assign')
        ->assertSee('Invite Staff')
        ->assertSee('Assign to house')
        ->assertSee('class="property-archive-table"', false)
        ->assertSee('Deactivate', false);
});

test('an owner cannot assign staff to another owners house', function () {
    $ownerRoleId = Role::where('name', 'Owner')->firstOrFail()->role_id;
    $owner = User::create([
        'role_id' => $ownerRoleId,
        'first_name' => 'First',
        'last_name' => 'Owner',
        'email' => 'first-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $otherOwner = User::create([
        'role_id' => $ownerRoleId,
        'first_name' => 'Other',
        'last_name' => 'Owner',
        'email' => 'other-owner@example.com',
        'password' => 'OwnerPass123!',
        'is_active' => true,
    ]);
    $otherHouse = TransientHouse::create([
        'owner_id' => $otherOwner->user_id,
        'name' => 'Other House',
        'address' => '2 Shoreline Road',
        'city' => 'Cebu City',
        'contact_number' => '5550002',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.admins.invite'), [
            'email' => 'unauthorized-staff@example.com',
            'house_id' => $otherHouse->house_id,
        ])
        ->assertSessionHasErrors('house_id');

    $this->assertDatabaseMissing('invitations', ['email' => 'unauthorized-staff@example.com']);
});
