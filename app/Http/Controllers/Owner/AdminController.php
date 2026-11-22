<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\TransientHouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $owner = Auth::user();
        $staffRole = Role::where('name', 'Staff')->firstOrFail();

        return view('owner.admins', [
            'houses' => TransientHouse::where('owner_id', $owner->user_id)
                ->where('is_active', true)
                ->whereNull('archived_at')
                ->orderBy('name')
                ->get(),
            'admins' => Invitation::where('invited_by', $owner->user_id)
                ->where('role_id', $staffRole->role_id)
                ->whereNotNull('used_at')
                ->with(['invitee', 'house'])
                ->orderByDesc('invitation_id')
                ->get(),
            'invitations' => Invitation::where('invited_by', $owner->user_id)
                ->where('role_id', $staffRole->role_id)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->with('house')
                ->orderByDesc('invitation_id')
                ->get(),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $owner = Auth::user();

        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'house_id' => ['required', 'integer'],
        ]);

        $house = TransientHouse::where('house_id', $data['house_id'])
            ->where('owner_id', $owner->user_id)
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->first();

        if (! $house) {
            return back()->withErrors(['house_id' => 'Invalid house selected.'])->withInput();
        }

        $staffRole = Role::where('name', 'Staff')->firstOrFail();

        $invitation = Invitation::create([
            'email' => $data['email'],
            'token' => Str::random(64),
            'role_id' => $staffRole->role_id,
            'invited_by' => $owner->user_id,
            'house_id' => $house->house_id,
            'expires_at' => now()->addHours(config('invitations.expiry_hours')),
        ]);

        session()->flash('success', "Invitation link is ready for {$data['email']}.");
        session()->flash('invite_link', route('register', ['token' => $invitation->token]));

        return redirect()->route('owner.admins.index');
    }

    public function remove(Invitation $invitation): RedirectResponse
    {
        $staffRole = Role::where('name', 'Staff')->firstOrFail();
        $deleted = Invitation::where('invitation_id', $invitation->invitation_id)
            ->where('invited_by', Auth::id())
            ->where('role_id', $staffRole->role_id)
            ->whereNotNull('used_at')
            ->delete();

        abort_if($deleted === 0, 404);

        session()->flash('success', 'Staff removed.');

        return redirect()->route('owner.admins.index');
    }
}
