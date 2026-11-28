<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function createSystemAdmin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return $user->role?->name === 'Account Admin'
                ? redirect()->route('accadmin.dashboard')
                : redirect($user->dashboardPath());
        }

        return view('auth.login', ['systemAdminLogin' => true]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->authenticate($request);
    }

    public function storeSystemAdmin(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return $user->role?->name === 'Account Admin'
                ? redirect()->route('accadmin.dashboard')
                : redirect($user->dashboardPath());
        }

        return $this->authenticate($request, true);
    }

    private function authenticate(Request $request, bool $systemAdminOnly = false): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('role')
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        if ($systemAdminOnly && $user->role?->name !== 'Account Admin') {
            throw ValidationException::withMessages([
                'email' => 'This sign-in is for System Admin accounts only.',
            ]);
        }

        if (! $user->is_active) {
            $message = $user->role?->name === 'Owner'
                ? 'Account Deactivated. Your account is currently deactivated and cannot access MCTBS. Contact the Account Admin to restore access.'
                : 'Account Deactivated. Your account is inactive. Contact your Owner to restore access.';

            throw ValidationException::withMessages(['email' => $message]);
        }

        if (! $user->email_verified_at && $user->role?->name !== 'Guest') {
            throw ValidationException::withMessages([
                'email' => 'Please verify your email before logging in.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        app(ConversationService::class)->claimVisitorConversations($user, $request);

        session()->flash('success', "Welcome back, {$user->first_name}!");

        if ($systemAdminOnly) {
            return redirect()->route('accadmin.dashboard');
        }

        return redirect()->intended($user->dashboardPath());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
