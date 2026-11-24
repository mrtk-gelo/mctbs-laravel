<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $user->role?->name === 'Owner'
                        ? 'Account Deactivated. Your account is currently deactivated. Contact the Account Admin to restore access.'
                        : 'Account Deactivated. Your account is inactive. Contact your Owner to restore access.',
                ]);
        }

        if ($user && $user->role?->name === 'Staff') {
            $parentOwnerId = DB::table('invitations')
                ->where('email', $user->email)
                ->whereNotNull('used_at')
                ->value('invited_by');

            if ($parentOwnerId && ! DB::table('users')->where('user_id', $parentOwnerId)->where('is_active', true)->exists()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'Account Deactivated. Your Owner account is inactive. Contact the Owner or Account Admin.']);
            }
        }

        if (! $user || ! $user->role || ! in_array($user->role->name, $this->roleNames($roles), true)) {
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }

    private function roleNames(array $roles): array
    {
        return array_map(function ($role) {
            return match ($role) {
                'accadmin', 'accountadmin' => 'Account Admin',
                'owner' => 'Owner',
                'staff' => 'Staff',
                'guest' => 'Guest',
                default => $role,
            };
        }, $roles);
    }
}
