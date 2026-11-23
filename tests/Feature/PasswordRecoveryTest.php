<?php

use App\Mail\PasswordResetLink;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

function createRecoveryUser(array $attributes = []): User
{
    return User::create(array_merge([
        'role_id' => Role::query()->where('name', 'Guest')->firstOrFail()->role_id,
        'first_name' => 'Recovery',
        'last_name' => 'Guest',
        'email' => 'recovery@example.com',
        'password' => 'ExistingPass123!',
        'is_active' => true,
    ], $attributes));
}

it('sends a designed reset email and gives the same confirmation for unknown addresses', function () {
    Mail::fake();
    $user = createRecoveryUser();

    $this->get(route('login'))
        ->assertSee('Forgot password?')
        ->assertSee(route('password.request'));

    $this->get(route('password.request'))
        ->assertSee('Enter the email address linked')
        ->assertSee('Send reset link');

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect(route('password.request'))
        ->assertSessionHas('status', 'If an active account exists for that email, a password reset link is on its way.');

    Mail::assertSent(PasswordResetLink::class, function (PasswordResetLink $mail) use ($user): bool {
        $mail->assertSeeInHtml('Reset password');
        $mail->assertSeeInText($mail->resetUrl);

        return $mail->hasTo($user->email);
    });

    $this->post(route('password.email'), ['email' => 'unknown@example.com'])
        ->assertSessionHas('status', 'If an active account exists for that email, a password reset link is on its way.');
    Mail::assertSent(PasswordResetLink::class, 1);
});

it('resets the password once and leaves the user signed out', function () {
    $user = createRecoveryUser();
    $token = Password::createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertSee('New password')
        ->assertSee('Confirm new password')
        ->assertSee('Show new password');

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewSecurePass123!',
        'password_confirmation' => 'NewSecurePass123!',
    ])->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Your password has been updated. You can now sign in.');

    expect(Hash::check('NewSecurePass123!', $user->fresh()->password))->toBeTrue();
    $this->assertGuest();
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'AnotherSecurePass123!',
        'password_confirmation' => 'AnotherSecurePass123!',
    ])->assertSessionHasErrors('email');
    expect(Hash::check('NewSecurePass123!', $user->fresh()->password))->toBeTrue();
});

it('rejects weak reset passwords and prevents inactive accounts from resetting', function () {
    $user = createRecoveryUser();
    $token = Password::createToken($user);

    $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors('password');

    $inactiveUser = createRecoveryUser([
        'email' => 'inactive@example.com',
        'is_active' => false,
    ]);
    $inactiveToken = Password::createToken($inactiveUser);
    Mail::fake();

    $this->post(route('password.email'), ['email' => $inactiveUser->email])
        ->assertSessionHas('status', 'If an active account exists for that email, a password reset link is on its way.');
    Mail::assertNothingSent();

    $this->post(route('password.update'), [
        'token' => $inactiveToken,
        'email' => $inactiveUser->email,
        'password' => 'AnotherSecurePass123!',
        'password_confirmation' => 'AnotherSecurePass123!',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('ExistingPass123!', $inactiveUser->fresh()->password))->toBeTrue();
});
