<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role || ! in_array($user->role->name, $this->roleNames($roles), true)) {
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }

    private function roleNames(array $roles): array
    {
        return array_map(function ($role) {
            return match ($role) {
                'sysadmin' => 'System Admin',
                'owner' => 'Owner',
                'staff' => 'Staff',
                'guest' => 'Guest',
                default => $role,
            };
        }, $roles);
    }
}
