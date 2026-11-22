<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('role');

        return view('auth.profile', ['user' => $user]);
    }

    public function security(Request $request): View
    {
        $user = $request->user()->load('role');

        return view('auth.security', ['user' => $user]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = $request->user();
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->phone = $validated['phone'] ?? null;
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return redirect()->route('profile.security')->with('success', 'Your password has been updated.');
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);
        $user = $request->user();
        abort_unless($user->role?->name === 'Owner', 403);

        DB::transaction(function () use ($user): void {
            $user->update([
                'is_active' => false,
                'deactivated_at' => now(),
                'deactivated_by' => $user->user_id,
            ]);
            DB::table('sessions')->where('user_id', $user->user_id)->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'Account Deactivated. Your account was deactivated successfully. Contact the System Administrator to restore access.',
        ]);
    }
}
