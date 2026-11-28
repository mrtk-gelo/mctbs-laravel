<?php

namespace App\Http\Controllers\Accadmin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('accadmin.dashboard', [
            'pendingOwnerInvitations' => Invitation::query()
                ->where('role_id', Role::where('name', 'Owner')->value('role_id'))
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->count(),
            'owners' => User::query()
                ->whereHas('role', fn ($query) => $query->where('name', 'Owner'))
                ->withCount('transientHouses')
                ->orderByDesc('registered_at')
                ->orderByDesc('user_id')
                ->get(),
        ]);
    }
}
