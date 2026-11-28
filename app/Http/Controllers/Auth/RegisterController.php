<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $token = $request->query('token');
        $invite = $this->validInvite($token);

        if (Auth::check() && ! $invite && ! $token) {
            return redirect(Auth::user()->dashboardPath());
        }

        return view('auth.register', [
            'token' => $token,
            'invite' => $invite,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::check() && ! $request->filled('token')) {
            return redirect(Auth::user()->dashboardPath());
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-\.]+$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-\.]+$/'],
            'phone' => ['nullable', 'string', 'max:30'],
            'terms_accepted' => ['accepted'],
            'email' => $request->filled('token')
                ? []
                : ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'token' => ['nullable', 'string'],
        ], [
            'terms_accepted.accepted' => 'You must accept the Terms and Conditions to create an account.',
        ]);

        $user = DB::transaction(function () use ($data): User {
            $invite = null;
            if (! empty($data['token'])) {
                $invite = Invitation::where('token', $data['token'])
                    ->lockForUpdate()
                    ->first();

                if (! $invite || $invite->used_at || ! $invite->expires_at->isFuture()) {
                    throw ValidationException::withMessages([
                        'token' => 'This invitation link is invalid or has expired.',
                    ]);
                }

                if ($invite->role->name === 'Staff') {
                    $assignedHouseIsValid = $invite->house_id
                        && $invite->house()
                            ->where('owner_id', $invite->invited_by)
                            ->exists();

                    if (! $assignedHouseIsValid) {
                        throw ValidationException::withMessages([
                            'token' => 'This staff invitation has no valid assigned house.',
                        ]);
                    }
                }

                if (User::where('email', $invite->email)->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'An account with this email already exists.',
                    ]);
                }
            }

            $roleId = $invite
                ? $invite->role_id
                : Role::where('name', 'Guest')->firstOrFail()->role_id;
            $email = $invite ? $invite->email : $data['email'];

            $user = User::create([
                'email' => $email,
                'password' => Hash::make($data['password']),
                'role_id' => $roleId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'email_verified_at' => $invite ? now() : null,
                'profile_image' => null,
                'is_active' => true,
            ]);

            if ($invite) {
                $invite->update(['used_at' => now()]);
            }

            return $user;
        });

        $user->load('role');

        Auth::login($user);
        $request->session()->regenerate();
        app(ConversationService::class)->claimVisitorConversations($user, $request);

        session()->flash('success', "Welcome to MCTBS, {$user->first_name}! Your account has been created.");

        return redirect($user->dashboardPath());
    }

    protected function validInvite(mixed $token): ?Invitation
    {
        if (! is_string($token) || $token === '') {
            return null;
        }

        return Invitation::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->with('role')
            ->first();
    }
}
