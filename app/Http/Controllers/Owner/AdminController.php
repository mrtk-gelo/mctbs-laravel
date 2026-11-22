<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $owner = Auth::user();
        $staffRole = Role::where('name', 'Staff')->firstOrFail();
        $search = trim((string) $request->query('q', ''));

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
                ->whereHas('invitee', fn ($query) => $query->where('is_active', true))
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('email', 'like', '%'.$search.'%')
                            ->orWhereHas('invitee', fn ($invitee) => $invitee
                                ->where('first_name', 'like', '%'.$search.'%')
                                ->orWhere('last_name', 'like', '%'.$search.'%'));
                    });
                })
                ->orderByDesc('invitation_id')
                ->get(),
            'invitations' => Invitation::where('invited_by', $owner->user_id)
                ->where('role_id', $staffRole->role_id)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->with('house')
                ->orderByDesc('invitation_id')
                ->get(),
            'search' => $search,
        ]);
    }

    public function deactivated(Request $request): View
    {
        $owner = Auth::user();
        $staffRole = Role::where('name', 'Staff')->firstOrFail();
        $search = trim((string) $request->query('q', ''));

        return view('owner.deactivated-admins', [
            'admins' => Invitation::where('invited_by', $owner->user_id)
                ->where('role_id', $staffRole->role_id)
                ->whereNotNull('used_at')
                ->with(['invitee', 'house'])
                ->whereHas('invitee', fn ($query) => $query->where('is_active', false))
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('email', 'like', '%'.$search.'%')
                            ->orWhereHas('invitee', fn ($invitee) => $invitee
                                ->where('first_name', 'like', '%'.$search.'%')
                                ->orWhere('last_name', 'like', '%'.$search.'%'));
                    });
                })
                ->orderByDesc('invitation_id')
                ->get(),
            'search' => $search,
        ]);
    }

    public function deactivate(Invitation $invitation): RedirectResponse
    {
        $staff = $this->ownedStaff($invitation);
        abort_if(! $staff->is_active, 422);

        DB::transaction(function () use ($staff): void {
            $staff->update([
                'is_active' => false,
                'deactivated_at' => now(),
                'deactivated_by' => Auth::id(),
            ]);
            DB::table('sessions')->where('user_id', $staff->user_id)->delete();
        });

        return redirect()->route('owner.admins.index')
            ->with('success', 'Staff account was deactivated.');
    }

    public function reactivate(Invitation $invitation): RedirectResponse
    {
        $owner = Auth::user();
        abort_if(! $owner->is_active, 422, 'Restore the owner account before reactivating staff.');

        $staff = $this->ownedStaff($invitation);
        abort_if($staff->is_active, 422);

        $staff->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
        ]);

        return redirect()->route('owner.admins.deactivated')
            ->with('success', 'Staff account was reactivated.');
    }

    public function permanentlyDelete(Invitation $invitation): RedirectResponse
    {
        $staff = $this->ownedStaff($invitation);
        abort_if($staff->is_active, 422, 'Only deactivated staff accounts can be permanently deleted.');

        $blockers = $this->staffDeletionBlockers($staff);
        if ($blockers !== []) {
            return redirect()->route('owner.admins.deactivated')
                ->with('error', "{$staff->first_name} {$staff->last_name} cannot be deleted while linked to ".implode(', ', $blockers).'. Keep the account deactivated.');
        }

        DB::transaction(function () use ($invitation, $staff): void {
            $invitation->delete();
            $staff->delete();
        });

        return redirect()->route('owner.admins.deactivated')
            ->with('success', 'Staff account was permanently deleted.');
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
        return $this->deactivate($invitation);
    }

    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_ids' => ['required', 'array', 'min:1', 'max:100'],
            'staff_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $count = DB::transaction(function () use ($data): int {
            $staff = $this->ownedStaffCollection($data['staff_ids']);

            foreach ($staff as $person) {
                $person->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                    'deactivated_by' => Auth::id(),
                ]);
                DB::table('sessions')->where('user_id', $person->user_id)->delete();
            }

            return $staff->count();
        });

        return redirect()->route('owner.admins.index')
            ->with('success', "{$count} staff account(s) deactivated.");
    }

    public function bulkReactivate(Request $request): RedirectResponse
    {
        $owner = Auth::user();
        abort_if(! $owner->is_active, 422, 'Restore the owner account before reactivating staff.');

        $data = $request->validate([
            'staff_ids' => ['required', 'array', 'min:1', 'max:100'],
            'staff_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $count = DB::transaction(function () use ($data): int {
            $staff = $this->ownedStaffCollection($data['staff_ids'], false);

            foreach ($staff as $person) {
                $person->update([
                    'is_active' => true,
                    'deactivated_at' => null,
                    'deactivated_by' => null,
                ]);
            }

            return $staff->count();
        });

        return redirect()->route('owner.admins.deactivated')
            ->with('success', "{$count} staff account(s) reactivated.");
    }

    public function bulkPermanentlyDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_ids' => ['required', 'array', 'min:1', 'max:100'],
            'staff_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $staff = $this->ownedStaffCollection($data['staff_ids'], false);
        $deleted = 0;
        $blocked = [];

        foreach ($staff as $person) {
            $blockers = $this->staffDeletionBlockers($person);
            if ($blockers !== []) {
                $blocked[] = "{$person->first_name} {$person->last_name}: linked to ".implode(', ', $blockers);

                continue;
            }

            DB::transaction(function () use ($person): void {
                Invitation::where('email', $person->email)
                    ->where('invited_by', Auth::id())
                    ->delete();
                $person->delete();
            });
            $deleted++;
        }

        $message = "{$deleted} staff account(s) deleted.";
        if ($blocked !== []) {
            $message .= ' Blocked: '.implode('; ', $blocked);
        }

        return redirect()->route('owner.admins.deactivated')->with(
            $blocked === [] ? 'success' : 'error',
            $message
        );
    }

    private function ownedStaff(Invitation $invitation): User
    {
        abort_unless(
            $invitation->invited_by === Auth::id()
            && $invitation->role_id === Role::where('name', 'Staff')->firstOrFail()->role_id,
            404
        );

        return User::query()
            ->where('email', $invitation->email)
            ->whereHas('role', fn ($query) => $query->where('name', 'Staff'))
            ->firstOrFail();
    }

    private function ownedStaffCollection(array $staffIds, bool $active = true)
    {
        $staffRole = Role::where('name', 'Staff')->firstOrFail();
        $emails = Invitation::where('invited_by', Auth::id())
            ->where('role_id', $staffRole->role_id)
            ->whereNotNull('used_at')
            ->pluck('email');

        return User::query()
            ->whereIn('user_id', $staffIds)
            ->whereIn('email', $emails)
            ->where('role_id', $staffRole->role_id)
            ->where('is_active', $active)
            ->lockForUpdate()
            ->get();
    }

    private function staffDeletionBlockers(User $staff): array
    {
        $references = [
            'payments' => ['processed_by', 'payment processing records'],
            'payment_refunds' => ['processed_by', 'refund records'],
            'discount_verifications' => ['verified_by', 'discount verification records'],
            'additional_charges' => ['created_by', 'checkout charge records'],
            'damage_reports' => ['reported_by', 'damage reports'],
            'booking_edits' => ['edited_by', 'booking edit history'],
            'transaction_archives' => ['archived_by', 'transaction archives'],
            'activity_logs' => ['user_id', 'activity logs'],
        ];
        $blockers = [];

        foreach ($references as $table => [$column, $label]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)
                && DB::table($table)->where($column, $staff->user_id)->exists()) {
                $blockers[] = $label;
            }
        }

        return $blockers;
    }
}
