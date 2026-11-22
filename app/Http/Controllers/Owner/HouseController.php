<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitPhoto;
use App\Services\PropertyArchiveService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class HouseController extends Controller
{
    public function index(): View
    {
        $houses = $this->accessibleHouses()
            ->whereNull('archived_at')
            ->withCount(['units' => fn (Builder $query) => $query->whereNull('archived_at')])
            ->orderByDesc('house_id')
            ->get();

        return view('owner.houses.index', ['houses' => $houses]);
    }

    public function archiveIndex(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $search = trim((string) ($filters['q'] ?? ''));
        $houseQuery = $this->accessibleHouses()
            ->with('archivedBy')
            ->withCount('units')
            ->whereNotNull('archived_at');
        $unitQuery = Unit::query()
            ->with(['house.archivedBy', 'archivedBy'])
            ->whereNotNull('archived_at')
            ->whereIn('house_id', $this->accessibleHouses()->select('house_id'));

        foreach (['from' => '>=', 'to' => '<='] as $field => $operator) {
            if (isset($filters[$field])) {
                $houseQuery->whereDate('archived_at', $operator, $filters[$field]);
                $unitQuery->whereDate('archived_at', $operator, $filters[$field]);
            }
        }

        if ($search !== '') {
            $houseQuery->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('barangay', 'like', "%{$search}%");
            });
            $unitQuery->where(function (Builder $query) use ($search): void {
                $query->where('unit_type', 'like', "%{$search}%")
                    ->orWhereHas('house', function (Builder $houseQuery) use ($search): void {
                        $houseQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('barangay', 'like', "%{$search}%");
                    });
            });
        }

        return view('owner.houses.archive', [
            'houses' => $houseQuery->orderByDesc('archived_at')->orderByDesc('house_id')->paginate(15, ['*'], 'housePage')->withQueryString(),
            'units' => $unitQuery->orderByDesc('archived_at')->orderByDesc('unit_id')->paginate(15, ['*'], 'unitPage')->withQueryString(),
            'filters' => $filters,
            'dashboardPrefix' => Auth::user()->role->name === 'Staff' ? 'staff' : 'owner',
        ]);
    }

    public function create(): View
    {
        return view('owner.houses.create');
    }

    public function edit(TransientHouse $house): View
    {
        $this->authorizeHouseAccess($house);

        return view('owner.houses.edit', ['house' => $house]);
    }

    public function archiveHouse(TransientHouse $house, PropertyArchiveService $archive): RedirectResponse
    {
        $this->authorizeHouseAccess($house);
        $archive->archiveHouse($house, Auth::user());

        return redirect()
            ->route($this->routeName('houses.index'))
            ->with('success', 'House archived. Its units were archived with it.');
    }

    public function archiveUnit(TransientHouse $house, Unit $unit, PropertyArchiveService $archive): RedirectResponse
    {
        $this->authorizeUnitAccess($house, $unit);
        $archive->archiveUnit($house, $unit, Auth::user());

        return redirect()
            ->route($this->routeName('houses.units'), $house)
            ->with('success', 'Unit archived.');
    }

    public function restoreHouse(TransientHouse $house, PropertyArchiveService $archive): RedirectResponse
    {
        $this->authorizeHouseAccess($house, true);
        $archive->restoreHouse($house);

        return redirect()
            ->route($this->routeName('houses.index'))
            ->with('success', 'House restored. Units archived with the house were restored too.');
    }

    public function restoreUnit(TransientHouse $house, Unit $unit, PropertyArchiveService $archive): RedirectResponse
    {
        $this->authorizeUnitAccess($house, $unit, true);
        $archive->restoreUnit($house, $unit);

        return redirect()
            ->route($this->routeName('houses.archive.index'))
            ->with('archiveResults', ["{$unit->unit_type} restored."]);
    }

    public function permanentlyDeleteHouse(TransientHouse $house, PropertyArchiveService $archive): RedirectResponse
    {
        $this->authorizeHouseAccess($house, true);
        $result = $archive->permanentlyDeleteHouse($house);
        $message = "{$house->name}: {$result['message']}";

        if ($result['cleanup_warning'] !== null) {
            $message .= ' '.$result['cleanup_warning'];
        }

        return redirect()
            ->route($this->routeName('houses.archive.index'))
            ->with('archiveResults', [$message]);
    }

    public function permanentlyDeleteUnit(TransientHouse $house, Unit $unit, PropertyArchiveService $archive): RedirectResponse
    {
        $this->authorizeUnitAccess($house, $unit, true);
        $result = $archive->permanentlyDeleteUnit($house, $unit);
        $message = "{$unit->unit_type}: {$result['message']}";

        if ($result['cleanup_warning'] !== null) {
            $message .= ' '.$result['cleanup_warning'];
        }

        return redirect()
            ->route($this->routeName('houses.archive.index'))
            ->with('archiveResults', [$message]);
    }

    public function bulkHouseArchiveAction(Request $request, PropertyArchiveService $archive): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['restore', 'delete'])],
            'house_ids' => ['required', 'array', 'min:1', 'max:50'],
            'house_ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $houseIds = array_map('intval', $data['house_ids']);
        $houses = $this->accessibleHouses()
            ->whereNotNull('archived_at')
            ->whereIn('house_id', $houseIds)
            ->get()
            ->keyBy('house_id');

        abort_unless($houses->count() === count($houseIds), 404);

        $results = [];
        foreach ($houseIds as $houseId) {
            $house = $houses->get($houseId);
            if ($data['action'] === 'restore') {
                try {
                    $archive->restoreHouse($house);
                    $results[] = "{$house->name} restored.";
                } catch (ValidationException $exception) {
                    $results[] = "{$house->name} not restored: ".$this->firstValidationError($exception);
                }

                continue;
            }

            $result = $archive->permanentlyDeleteHouse($house);
            $results[] = "{$house->name}: {$result['message']}".($result['cleanup_warning'] ? ' '.$result['cleanup_warning'] : '');
        }

        return redirect()
            ->route($this->routeName('houses.archive.index'))
            ->with('archiveResults', $results);
    }

    public function bulkUnitArchiveAction(Request $request, PropertyArchiveService $archive): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['restore', 'delete'])],
            'unit_ids' => ['required', 'array', 'min:1', 'max:50'],
            'unit_ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $unitIds = array_map('intval', $data['unit_ids']);
        $units = Unit::query()
            ->with('house')
            ->whereNotNull('archived_at')
            ->whereIn('unit_id', $unitIds)
            ->whereIn('house_id', $this->accessibleHouses()->select('house_id'))
            ->get()
            ->keyBy('unit_id');

        abort_unless($units->count() === count($unitIds), 404);

        $results = [];
        foreach ($unitIds as $unitId) {
            $unit = $units->get($unitId);
            $house = $unit->house;
            if ($data['action'] === 'restore') {
                try {
                    $archive->restoreUnit($house, $unit);
                    $results[] = "{$unit->unit_type} at {$house->name} restored.";
                } catch (ValidationException $exception) {
                    $results[] = "{$unit->unit_type} at {$house->name} not restored: ".$this->firstValidationError($exception);
                }

                continue;
            }

            $result = $archive->permanentlyDeleteUnit($house, $unit);
            $results[] = "{$unit->unit_type} at {$house->name}: {$result['message']}"
                .($result['cleanup_warning'] ? ' '.$result['cleanup_warning'] : '');
        }

        return redirect()
            ->route($this->routeName('houses.archive.index'))
            ->with('archiveResults', $results);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['required', 'string', 'max:30'],
            'policies' => ['nullable', 'array'],
            'policies.*' => ['nullable', 'string', 'max:1000'],
            'social_links' => ['nullable', 'array', 'max:10'],
            'social_links.*.name' => ['nullable', 'string', 'max:50', 'required_with:social_links.*.url'],
            'social_links.*.url' => ['nullable', 'string', 'max:2048', 'regex:/^https?:\\/\\/.+/i', 'required_with:social_links.*.name'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $data['policies'] = $this->validatedPolicies($data['policies'] ?? []);
        $data['social_links'] = $this->validatedSocialLinks($data['social_links'] ?? []);

        if ($request->hasFile('cover_photo')) {
            $coverPhoto = $request->file('cover_photo')->store('houses', 'public');

            if ($coverPhoto === false) {
                return back()->withInput()->withErrors(['cover_photo' => 'The cover photo could not be saved.']);
            }

            $data['cover_photo'] = $coverPhoto;
        }

        TransientHouse::create([
            ...$data,
            'owner_id' => Auth::user()->user_id,
            'is_active' => true,
        ]);

        return redirect()
            ->route('owner.houses.index')
            ->with('success', 'Transient house added successfully.');
    }

    public function update(Request $request, TransientHouse $house): RedirectResponse
    {
        $this->authorizeHouseAccess($house);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['required', 'string', 'max:30'],
            'policies' => ['nullable', 'array'],
            'policies.*' => ['nullable', 'string', 'max:1000'],
            'social_links' => ['nullable', 'array', 'max:10'],
            'social_links.*.name' => ['nullable', 'string', 'max:50', 'required_with:social_links.*.url'],
            'social_links.*.url' => ['nullable', 'string', 'max:2048', 'regex:/^https?:\\/\\/.+/i', 'required_with:social_links.*.name'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_cover_photo' => ['nullable', 'boolean'],
        ]);

        $data['policies'] = $this->validatedPolicies($data['policies'] ?? []);
        $data['social_links'] = $this->validatedSocialLinks($data['social_links'] ?? []);
        unset($data['remove_cover_photo']);

        if ($request->boolean('remove_cover_photo') && $house->cover_photo) {
            Storage::disk('public')->delete($house->cover_photo);
            $data['cover_photo'] = null;
        }

        if ($request->hasFile('cover_photo')) {
            $coverPhoto = $request->file('cover_photo')->store('houses', 'public');

            if ($coverPhoto === false) {
                return back()->withInput()->withErrors(['cover_photo' => 'The cover photo could not be saved.']);
            }

            if ($house->cover_photo) {
                Storage::disk('public')->delete($house->cover_photo);
            }

            $data['cover_photo'] = $coverPhoto;
        }

        $house->update($data);

        return redirect()
            ->route($this->routeName('houses.index'))
            ->with('success', 'Transient house updated successfully.');
    }

    public function updateHouseAvailability(Request $request, TransientHouse $house): RedirectResponse
    {
        $this->authorizeHouseAccess($house);

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $house->update(['is_active' => $data['is_active']]);

        return back()->with('success', $house->is_active
            ? 'House is now visible on the public site.'
            : 'House has been hidden from the public site.');
    }

    public function units(TransientHouse $house): View
    {
        $this->authorizeHouseAccess($house);

        return view('owner.houses.units', [
            'house' => $house,
            'units' => $house->units()->with('photos')->whereNull('archived_at')->orderByDesc('unit_id')->get(),
        ]);
    }

    public function createUnit(TransientHouse $house): View
    {
        $this->authorizeHouseAccess($house);

        return view('owner.houses.create-unit', ['house' => $house]);
    }

    public function editUnit(TransientHouse $house, Unit $unit): View
    {
        $this->authorizeUnitAccess($house, $unit);

        return view('owner.houses.edit-unit', [
            'house' => $house,
            'unit' => $unit->load('photos', 'addons', 'damageItems'),
        ]);
    }

    public function storeUnit(Request $request, TransientHouse $house): RedirectResponse
    {
        $this->authorizeHouseAccess($house);

        $data = $request->validate([
            'unit_type' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'maximum_capacity' => ['sometimes', 'required', 'integer', 'gte:capacity'],
            'check_in_time' => ['sometimes', 'required', 'date_format:H:i'],
            'check_out_time' => ['sometimes', 'required', 'date_format:H:i'],
            'pricing_mode' => ['sometimes', 'required', Rule::in(['Per Unit', 'Per Head'])],
            'extra_guest_fee' => ['sometimes', 'required', 'numeric', 'min:0'],
            'child_free_through_age' => ['nullable', 'integer', 'between:0,17'],
            'price' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'string', Rule::in(['Available', 'Unavailable', 'Booked'])],
            'description' => ['nullable', 'string'],
            'addons' => ['sometimes', 'array'],
            'addons.*.name' => ['required', 'string', 'max:100'],
            'addons.*.price' => ['required', 'numeric', 'min:0'],
            'damage_items' => ['sometimes', 'array'],
            'damage_items.*.name' => ['required', 'string', 'max:100'],
            'damage_items.*.price' => ['required', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $data['check_in_time'] ??= '14:00';
        $data['check_out_time'] ??= '12:00';
        $data['pricing_mode'] ??= 'Per Unit';
        if (! $this->hasMinimumTurnoverInterval($data['check_out_time'], $data['check_in_time'])) {
            throw ValidationException::withMessages([
                'check_out_time' => 'Check-in time must be at least two hours after check-out time.',
            ]);
        }
        $data['maximum_capacity'] ??= $data['capacity'];
        $data['extra_guest_fee'] ??= 0;
        $addons = $data['addons'] ?? [];
        $damageItems = $data['damage_items'] ?? [];
        $photos = $data['photos'] ?? [];
        unset($data['addons'], $data['damage_items'], $data['photos']);

        $storedPhotoPaths = [];
        try {
            DB::transaction(function () use ($house, $data, $addons, $damageItems, $photos, &$storedPhotoPaths): void {
                $unit = $house->units()->create($data);
                $unit->addons()->createMany($addons);
                $unit->damageItems()->createMany($damageItems);
                $this->attachPhotos($unit, $photos, $storedPhotoPaths);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPhotoPaths);

            throw $exception;
        }

        return redirect()
            ->route($this->routeName('houses.units'), $house)
            ->with('success', 'Unit added successfully.');
    }

    public function updateUnit(Request $request, TransientHouse $house, Unit $unit): RedirectResponse
    {
        $this->authorizeUnitAccess($house, $unit);

        $data = $request->validate([
            'unit_type' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'maximum_capacity' => ['sometimes', 'required', 'integer', 'gte:capacity'],
            'check_in_time' => ['sometimes', 'required', 'date_format:H:i'],
            'check_out_time' => ['sometimes', 'required', 'date_format:H:i'],
            'pricing_mode' => ['sometimes', 'required', Rule::in(['Per Unit', 'Per Head'])],
            'extra_guest_fee' => ['sometimes', 'required', 'numeric', 'min:0'],
            'child_free_through_age' => ['nullable', 'integer', 'between:0,17'],
            'price' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'string', Rule::in(['Available', 'Unavailable', 'Booked'])],
            'description' => ['nullable', 'string'],
            'addons' => ['sometimes', 'array'],
            'addons.*.name' => ['required', 'string', 'max:100'],
            'addons.*.price' => ['required', 'numeric', 'min:0'],
            'damage_items' => ['sometimes', 'array'],
            'damage_items.*.name' => ['required', 'string', 'max:100'],
            'damage_items.*.price' => ['required', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_photos' => ['nullable', 'array', 'max:10'],
            'remove_photos.*' => ['required', 'integer'],
        ]);

        $data['check_in_time'] ??= substr((string) $unit->check_in_time, 0, 5);
        $data['check_out_time'] ??= substr((string) $unit->check_out_time, 0, 5);
        $data['pricing_mode'] ??= $unit->pricing_mode ?? 'Per Unit';
        if (! $this->hasMinimumTurnoverInterval($data['check_out_time'], $data['check_in_time'])) {
            throw ValidationException::withMessages([
                'check_out_time' => 'Check-in time must be at least two hours after check-out time.',
            ]);
        }
        $data['maximum_capacity'] ??= $data['capacity'];
        $data['extra_guest_fee'] ??= $unit->extra_guest_fee ?? 0;
        $addons = $data['addons'] ?? [];
        $damageItems = $data['damage_items'] ?? [];
        $photos = $data['photos'] ?? [];
        $removePhotoIds = array_unique(array_map('intval', $data['remove_photos'] ?? []));
        unset($data['addons'], $data['damage_items'], $data['photos'], $data['remove_photos']);

        $photosToRemove = $unit->photos()->whereIn('photo_id', $removePhotoIds)->get();

        abort_unless($photosToRemove->count() === count($removePhotoIds), 404);

        if ($unit->photos()->count() - $photosToRemove->count() + count($photos) > 10) {
            throw ValidationException::withMessages([
                'photos' => 'A unit can have no more than 10 photos.',
            ]);
        }

        $storedPhotoPaths = [];
        try {
            DB::transaction(function () use ($unit, $data, $addons, $damageItems, $photosToRemove, $photos, &$storedPhotoPaths): void {
                $unit->update($data);
                $unit->addons()->delete();
                $unit->addons()->createMany($addons);
                $unit->damageItems()->delete();
                $unit->damageItems()->createMany($damageItems);
                $unit->photos()->whereIn('photo_id', $photosToRemove->modelKeys())->delete();

                if (! $unit->photos()->where('is_cover', true)->exists()) {
                    $unit->photos()->orderBy('sort_order')->first()?->update(['is_cover' => true]);
                }

                $this->attachPhotos($unit, $photos, $storedPhotoPaths);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPhotoPaths);

            throw $exception;
        }

        foreach ($photosToRemove as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        return redirect()
            ->route($this->routeName('houses.units'), $house)
            ->with('success', 'Unit updated successfully.');
    }

    public function updateUnitAvailability(Request $request, TransientHouse $house, Unit $unit): RedirectResponse
    {
        $this->authorizeUnitAccess($house, $unit);

        abort_unless(in_array($unit->status, ['Available', 'Unavailable'], true), 422);

        $data = $request->validate([
            'status' => ['required', Rule::in(['Available', 'Unavailable'])],
        ]);

        $unit->update($data);

        return back()->with('success', $unit->status === 'Available'
            ? 'Unit is now available on the public site.'
            : 'Unit has been hidden from the public site.');
    }

    private function authorizeUnitAccess(TransientHouse $house, Unit $unit, bool $allowArchived = false): void
    {
        $this->authorizeHouseAccess($house, $allowArchived);

        abort_unless($unit->house_id === $house->house_id, 404);
        abort_unless($allowArchived || $unit->archived_at === null, 404);
    }

    private function hasMinimumTurnoverInterval(string $checkOutTime, string $checkInTime): bool
    {
        [$checkOutHours, $checkOutMinutes] = array_map('intval', explode(':', $checkOutTime));
        [$checkInHours, $checkInMinutes] = array_map('intval', explode(':', $checkInTime));
        $checkOutTotalMinutes = ($checkOutHours * 60) + $checkOutMinutes;
        $checkInTotalMinutes = ($checkInHours * 60) + $checkInMinutes;

        return $checkInTotalMinutes - $checkOutTotalMinutes >= 120;
    }

    /**
     * @param  array<int, UploadedFile>  $photos
     * @param  array<int, string>  $storedPhotoPaths
     */
    private function attachPhotos(Unit $unit, array $photos, array &$storedPhotoPaths): void
    {
        $maxSortOrder = $unit->photos()->max('sort_order');
        $sortOrder = $maxSortOrder === null ? 0 : (int) $maxSortOrder + 1;
        $isCover = ! $unit->photos()->exists();

        foreach ($photos as $photo) {
            $photoPath = $photo->store('units/'.$unit->unit_id, 'public');

            if ($photoPath === false) {
                throw new RuntimeException('A unit photo could not be saved.');
            }

            $storedPhotoPaths[] = $photoPath;
            UnitPhoto::create([
                'unit_id' => $unit->unit_id,
                'photo_path' => $photoPath,
                'is_cover' => $isCover,
                'sort_order' => $sortOrder++,
            ]);
            $isCover = false;
        }
    }

    private function authorizeHouseAccess(TransientHouse $house, bool $allowArchived = false): void
    {
        abort_unless($allowArchived || $house->archived_at === null, 404);

        $user = Auth::user();

        if ($user->role->name === 'Owner') {
            abort_unless($house->owner_id === $user->user_id, 404);

            return;
        }

        $hasAssignment = $this->staffAssignments($user->email)
            ->where('house_id', $house->house_id)
            ->where('invited_by', $house->owner_id)
            ->exists();

        abort_unless($hasAssignment, 404);
    }

    private function routeName(string $name): string
    {
        return Auth::user()->role->name === 'Staff'
            ? 'staff.'.$name
            : 'owner.'.$name;
    }

    private function accessibleHouses(): Builder
    {
        $user = Auth::user();

        return TransientHouse::query()
            ->when($user->role->name === 'Owner', function (Builder $query) use ($user): void {
                $query->where('owner_id', $user->user_id);
            }, function (Builder $query) use ($user): void {
                $assignedHouseIds = $this->staffAssignments($user->email)->pluck('house_id');
                $query->whereIn('house_id', $assignedHouseIds);
            });
    }

    private function firstValidationError(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? 'The operation could not be completed.';
    }

    private function staffAssignments(string $email): Builder
    {
        $staffRoleId = Role::where('name', 'Staff')->firstOrFail()->role_id;

        return Invitation::query()
            ->where('email', $email)
            ->where('role_id', $staffRoleId)
            ->whereNotNull('used_at')
            ->whereNotNull('house_id')
            ->whereHas('house', function (Builder $query): void {
                $query->whereColumn('transient_houses.owner_id', 'invitations.invited_by');
            });
    }

    /**
     * @param  array<int, array{name?: ?string, url?: ?string}>  $socialLinks
     * @return array<int, array{name: string, url: string}>
     */
    private function validatedSocialLinks(array $socialLinks): array
    {
        return collect($socialLinks)
            ->filter(fn (array $link): bool => filled($link['name'] ?? null) && filled($link['url'] ?? null))
            ->map(fn (array $link): array => [
                'name' => trim($link['name']),
                'url' => trim($link['url']),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, ?string>  $policies
     */
    private function validatedPolicies(array $policies): ?string
    {
        $policyText = collect($policies)
            ->map(fn (?string $policy): string => trim($policy ?? ''))
            ->filter(fn (string $policy): bool => $policy !== '')
            ->implode("\n");

        return $policyText !== '' ? $policyText : null;
    }
}
