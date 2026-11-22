<?php

namespace App\Http\Controllers\Sysadmin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['q'] ?? '');
        $owners = User::whereHas('role', fn ($query) => $query->where('name', 'Owner'))
            ->where('is_active', true)
            ->withCount('transientHouses')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('user_id')
            ->get();

        if ($request->ajax()) {
            return view('sysadmin.partials.owner-results', compact('owners', 'search'));
        }

        return view('sysadmin.owners', [
            'owners' => $owners,
            'search' => $search,
            'invitations' => Invitation::whereHas('role', fn ($query) => $query->where('name', 'Owner'))
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->orderByDesc('invitation_id')
                ->get(),
        ]);
    }

    public function deactivated(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['q'] ?? '');
        $owners = User::whereHas('role', fn ($query) => $query->where('name', 'Owner'))
            ->where('is_active', false)
            ->withCount('transientHouses')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('deactivated_at')
            ->get();

        if ($request->ajax()) {
            return view('sysadmin.partials.deactivated-owner-results', compact('owners', 'search'));
        }

        return view('sysadmin.deactivated-owners', compact('owners', 'search'));
    }

    public function deactivate(int $ownerId): RedirectResponse
    {
        $owner = $this->ownedAccount($ownerId);
        abort_if(! $owner->is_active, 422);

        DB::transaction(function () use ($owner): void {
            $owner->update([
                'is_active' => false,
                'deactivated_at' => now(),
                'deactivated_by' => auth()->id(),
            ]);
            DB::table('sessions')->where('user_id', $owner->user_id)->delete();
        });

        return redirect()->route('sysadmin.owners.index')
            ->with('success', "Owner account for {$owner->first_name} {$owner->last_name} was deactivated.");
    }

    public function reactivate(int $ownerId): RedirectResponse
    {
        $owner = $this->ownedAccount($ownerId);
        abort_if($owner->is_active, 422);

        $owner->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
        ]);

        return redirect()->route('sysadmin.owners.deactivated')
            ->with('success', "Owner account for {$owner->first_name} {$owner->last_name} was reactivated.");
    }

    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $ownerRole = Role::query()->where('name', 'Owner')->firstOrFail();
        $data = $request->validate([
            'owner_ids' => ['required', 'array', 'min:1', 'max:100'],
            'owner_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'user_id')->where('role_id', $ownerRole->role_id)],
        ]);

        $count = DB::transaction(function () use ($data): int {
            $owners = User::query()
                ->whereIn('user_id', $data['owner_ids'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();

            foreach ($owners as $owner) {
                $owner->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                    'deactivated_by' => auth()->id(),
                ]);
                DB::table('sessions')->where('user_id', $owner->user_id)->delete();
            }

            return $owners->count();
        });

        return redirect()->route('sysadmin.owners.index')
            ->with('success', "{$count} owner account(s) deactivated.");
    }

    public function bulkReactivate(Request $request): RedirectResponse
    {
        $ownerRole = Role::query()->where('name', 'Owner')->firstOrFail();
        $data = $request->validate([
            'owner_ids' => ['required', 'array', 'min:1', 'max:100'],
            'owner_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'user_id')->where('role_id', $ownerRole->role_id)],
        ]);

        $count = DB::transaction(function () use ($data): int {
            $owners = User::query()
                ->whereIn('user_id', $data['owner_ids'])
                ->where('is_active', false)
                ->lockForUpdate()
                ->get();

            foreach ($owners as $owner) {
                $owner->update([
                    'is_active' => true,
                    'deactivated_at' => null,
                    'deactivated_by' => null,
                ]);
            }

            return $owners->count();
        });

        return redirect()->route('sysadmin.owners.deactivated')
            ->with('success', "{$count} owner account(s) reactivated.");
    }

    public function invite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')],
        ]);

        $ownerRole = Role::where('name', 'Owner')->firstOrFail();

        // Replace any earlier unused invite to the same email
        Invitation::where('email', $data['email'])
            ->where('role_id', $ownerRole->role_id)
            ->whereNull('used_at')
            ->delete();

        $invitation = Invitation::create([
            'email' => $data['email'],
            'token' => Str::random(64),
            'role_id' => $ownerRole->role_id,
            'invited_by' => auth()->id(),
            'house_id' => null,
            'expires_at' => now()->addHours(config('invitations.expiry_hours')),
        ]);

        session()->flash('success', "Invitation link is ready for {$invitation->email}.");
        session()->flash('invite_link', route('register', ['token' => $invitation->token]));

        return redirect()->route('sysadmin.owners.index');
    }

    public function destroy(int $ownerId): RedirectResponse
    {
        $ownerRole = Role::query()->where('name', 'Owner')->firstOrFail();
        $owner = User::query()
            ->where('role_id', $ownerRole->role_id)
            ->findOrFail($ownerId);
        abort_if($owner->is_active, 422, 'Only deactivated owner accounts can be permanently deleted.');
        $ownerRoleId = $ownerRole->role_id;

        $blockers = DB::transaction(function () use ($owner, $ownerRoleId): array {
            $lockedOwner = User::query()
                ->where('role_id', $ownerRoleId)
                ->whereKey($owner->user_id)
                ->lockForUpdate()
                ->firstOrFail();
            $blockers = $this->deletionBlockers(collect([$lockedOwner]))[$lockedOwner->user_id];

            if ($blockers === []) {
                $lockedOwner->delete();
            }

            return $blockers;
        });

        if ($blockers !== []) {
            return redirect()
                ->route('sysadmin.owners.deactivated')
                ->with('error', $this->deletionBlockedMessage($owner, $blockers));
        }

        return redirect()
            ->route('sysadmin.owners.deactivated')
            ->with('success', "Owner account for {$owner->first_name} {$owner->last_name} was deleted.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ownerRole = Role::query()->where('name', 'Owner')->firstOrFail();
        $data = $request->validate([
            'owner_ids' => ['required', 'array', 'min:1', 'max:100'],
            'owner_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'user_id')->where('role_id', $ownerRole->role_id),
            ],
        ]);

        $result = DB::transaction(function () use ($data, $ownerRole): array {
            $owners = User::query()
                ->whereIn('user_id', $data['owner_ids'])
                ->orderBy('user_id')
                ->lockForUpdate()
                ->get();
            if ($owners->count() !== count($data['owner_ids'])
                || $owners->contains(fn (User $owner): bool => (int) $owner->role_id !== (int) $ownerRole->role_id)) {
                return ['blocked' => ['The selected owner accounts changed. Refresh the list and try again.'], 'deleted' => 0];
            }
            $activeOwners = $owners->where('is_active', true);
            if ($activeOwners->isNotEmpty()) {
                return ['blocked' => ['Only deactivated owner accounts can be permanently deleted.'], 'deleted' => 0];
            }

            $blockedOwners = [];
            $blockersByOwner = $this->deletionBlockers($owners);

            foreach ($owners as $owner) {
                $blockers = $blockersByOwner[$owner->user_id];

                if ($blockers !== []) {
                    $blockedOwners[] = $this->deletionBlockedMessage($owner, $blockers);
                }
            }

            if ($blockedOwners !== []) {
                return ['blocked' => $blockedOwners, 'deleted' => 0];
            }

            foreach ($owners as $owner) {
                $owner->delete();
            }

            return ['blocked' => [], 'deleted' => $owners->count()];
        });

        if ($result['blocked'] !== []) {
            return redirect()
                ->route('sysadmin.owners.deactivated')
                ->with('error', 'No accounts were deleted. '.$this->joinDeletionMessages($result['blocked']));
        }

        return redirect()
            ->route('sysadmin.owners.deactivated')
            ->with('success', $result['deleted'].' owner account(s) deleted.');
    }

    private function ownedAccount(int $ownerId): User
    {
        $ownerRole = Role::query()->where('name', 'Owner')->firstOrFail();

        return User::query()
            ->where('role_id', $ownerRole->role_id)
            ->findOrFail($ownerId);
    }

    /**
     * @param  Collection<int, User>  $owners
     * @return array<int, array<int, string>>
     */
    private function deletionBlockers(Collection $owners): array
    {
        $references = [
            'transient_houses' => ['owner_id', 'properties'],
            'bookings' => ['user_id', 'guest bookings'],
            'additional_charges' => ['created_by', 'checkout charges'],
            'damage_reports' => ['reported_by', 'damage reports'],
            'feedback' => ['user_id', 'feedback records'],
            'messages' => ['sender_id', 'sent messages'],
            'invitations' => ['invited_by', 'invitations'],
            'transaction_archives' => ['archived_by', 'transaction archives'],
            'reports' => ['generated_by', 'generated reports'],
            'booking_edits' => ['edited_by', 'booking edit history'],
        ];
        $ownerIds = $owners->pluck('user_id')->all();
        $blockers = $owners->mapWithKeys(fn (User $owner): array => [$owner->user_id => []])->all();

        foreach ($references as $table => [$column, $label]) {
            $referencingOwnerIds = DB::table($table)
                ->whereIn($column, $ownerIds)
                ->distinct()
                ->pluck($column);

            foreach ($referencingOwnerIds as $ownerId) {
                $blockers[$ownerId][] = $label;
            }
        }

        return $blockers;
    }

    /**
     * @param  array<int, string>  $blockers
     */
    private function deletionBlockedMessage(User $owner, array $blockers): string
    {
        return "{$owner->first_name} {$owner->last_name} cannot be deleted while linked to ".$this->joinDeletionMessages($blockers).'. Reassign or remove those records first.';
    }

    /**
     * @param  array<int, string>  $messages
     */
    private function joinDeletionMessages(array $messages): string
    {
        if (count($messages) < 2) {
            return $messages[0] ?? '';
        }

        $lastMessage = array_pop($messages);

        return implode(', ', $messages).' and '.$lastMessage;
    }
}
