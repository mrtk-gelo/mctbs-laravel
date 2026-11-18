<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
