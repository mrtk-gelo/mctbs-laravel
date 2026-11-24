<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        try {
            Password::sendResetLink(['email' => $validated['email']]);
        } catch (Throwable $exception) {
            Log::warning('Password reset email delivery failed.', [
                'email_hash' => hash('sha256', strtolower($validated['email'])),
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not send a reset link right now. Please try again later.']);
        }

        return back()->with(
            'status',
            'If an active account exists for that email, a password reset link is on its way.'
        );
    }

    public function resetForm(Request $request): View
    {
        return view('auth.reset-password', [
            'token' => $request->route('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:191'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This reset link is invalid or has expired.']);
        }

        $status = DB::transaction(function () use ($validated, $request): string {
            return Password::reset(
                [
                    'email' => $validated['email'],
                    'token' => $validated['token'],
                    'password' => $validated['password'],
                    'password_confirmation' => $request->input('password_confirmation'),
                ],
                function (User $user, string $password): void {
                    $activeUser = User::query()
                        ->lockForUpdate()
                        ->findOrFail($user->user_id);

                    if (! $activeUser->is_active) {
                        throw ValidationException::withMessages([
                            'email' => 'This reset link is invalid or has expired.',
                        ]);
                    }

                    $activeUser->forceFill(['password' => $password])->save();
                    DB::table('sessions')->where('user_id', $activeUser->user_id)->delete();
                }
            );
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Your password has been updated. You can now sign in.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'This reset link is invalid or has expired.']);
    }
}
