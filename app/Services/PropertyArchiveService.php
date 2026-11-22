<?php

namespace App\Services;

use App\Models\Report;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitPhoto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PropertyArchiveService
{
    public function archiveHouse(TransientHouse $house, User $actor): void
    {
        DB::transaction(function () use ($house, $actor): void {
            $lockedHouse = TransientHouse::query()
                ->whereKey($house->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedHouse->archived_at !== null) {
                throw ValidationException::withMessages(['archive' => 'This house is already archived.']);
            }

            $archivedAt = now();
            $lockedHouse->forceFill([
                'archived_at' => $archivedAt,
                'archived_by' => $actor->user_id,
            ])->save();

            Unit::query()
                ->where('house_id', $lockedHouse->house_id)
                ->whereNull('archived_at')
                ->update([
                    'archived_at' => $archivedAt,
                    'archived_by' => $actor->user_id,
                    'archived_with_house' => true,
                ]);
        });
    }

    public function restoreHouse(TransientHouse $house): void
    {
        DB::transaction(function () use ($house): void {
            $lockedHouse = TransientHouse::query()
                ->whereKey($house->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedHouse->archived_at === null) {
                throw ValidationException::withMessages(['archive' => 'This house is not archived.']);
            }

            $lockedHouse->forceFill([
                'archived_at' => null,
                'archived_by' => null,
            ])->save();

            Unit::query()
                ->where('house_id', $lockedHouse->house_id)
                ->where('archived_with_house', true)
                ->update([
                    'archived_at' => null,
                    'archived_by' => null,
                    'archived_with_house' => false,
                ]);
        });
    }

    public function archiveUnit(TransientHouse $house, Unit $unit, User $actor): void
    {
        DB::transaction(function () use ($house, $unit, $actor): void {
            $lockedHouse = TransientHouse::query()
                ->whereKey($house->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUnit = Unit::query()
                ->whereKey($unit->unit_id)
                ->where('house_id', $lockedHouse->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedHouse->archived_at !== null) {
                throw ValidationException::withMessages(['archive' => 'Restore the house before archiving an individual unit.']);
            }

            if ($lockedUnit->archived_at !== null) {
                throw ValidationException::withMessages(['archive' => 'This unit is already archived.']);
            }

            $lockedUnit->forceFill([
                'archived_at' => now(),
                'archived_by' => $actor->user_id,
                'archived_with_house' => false,
            ])->save();
        });
    }

    public function restoreUnit(TransientHouse $house, Unit $unit): void
    {
        DB::transaction(function () use ($house, $unit): void {
            $lockedHouse = TransientHouse::query()
                ->whereKey($house->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUnit = Unit::query()
                ->whereKey($unit->unit_id)
                ->where('house_id', $lockedHouse->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedHouse->archived_at !== null) {
                throw ValidationException::withMessages(['archive' => 'Restore the house before restoring an individual unit.']);
            }

            if ($lockedUnit->archived_at === null) {
                throw ValidationException::withMessages(['archive' => 'This unit is not archived.']);
            }

            $lockedUnit->forceFill([
                'archived_at' => null,
                'archived_by' => null,
                'archived_with_house' => false,
            ])->save();
        });
    }

    /**
     * @return array{deleted: bool, message: string, cleanup_warning: ?string}
     */
    public function permanentlyDeleteHouse(TransientHouse $house): array
    {
        $result = DB::transaction(function () use ($house): array {
            $lockedHouse = TransientHouse::query()
                ->whereKey($house->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedHouse->archived_at === null) {
                return $this->blocked('Only archived houses can be permanently deleted.');
            }

            if (Report::query()->where('house_id', $lockedHouse->house_id)->exists()) {
                return $this->blocked('The house has reports linked to it.');
            }

            if ($lockedHouse->invitations()->exists()) {
                return $this->blocked('The house has staff invitations linked to it.');
            }

            $units = $lockedHouse->units()->lockForUpdate()->get();
            foreach ($units as $unit) {
                if ($unit->archived_at === null) {
                    return $this->blocked('The house contains a unit that has not been archived.');
                }

                $reason = $this->unitHistoryBlockReason($unit);

                if ($reason !== null) {
                    return $this->blocked("{$unit->unit_type} cannot be deleted: {$reason}");
                }
            }

            $photoPaths = $units
                ->flatMap(fn (Unit $unit) => $unit->photos()->pluck('photo_path'))
                ->push($lockedHouse->cover_photo)
                ->filter()
                ->unique()
                ->values()
                ->all();

            foreach ($units as $unit) {
                $unit->delete();
            }

            $lockedHouse->delete();

            return [
                'deleted' => true,
                'message' => 'House permanently deleted.',
                'photo_paths' => $photoPaths,
            ];
        });

        if (! $result['deleted']) {
            return $result;
        }

        return $this->deleteUnreferencedPhotos($result);
    }

    /**
     * @return array{deleted: bool, message: string, cleanup_warning: ?string}
     */
    public function permanentlyDeleteUnit(TransientHouse $house, Unit $unit): array
    {
        $result = DB::transaction(function () use ($house, $unit): array {
            $lockedHouse = TransientHouse::query()
                ->whereKey($house->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUnit = Unit::query()
                ->whereKey($unit->unit_id)
                ->where('house_id', $lockedHouse->house_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedUnit->archived_at === null) {
                return $this->blocked('Only archived units can be permanently deleted.');
            }

            $reason = $this->unitHistoryBlockReason($lockedUnit);
            if ($reason !== null) {
                return $this->blocked("The unit cannot be deleted: {$reason}");
            }

            $photoPaths = $lockedUnit->photos()->pluck('photo_path')->unique()->values()->all();
            $lockedUnit->delete();

            return [
                'deleted' => true,
                'message' => 'Unit permanently deleted.',
                'photo_paths' => $photoPaths,
            ];
        });

        if (! $result['deleted']) {
            return $result;
        }

        return $this->deleteUnreferencedPhotos($result);
    }

    private function unitHistoryBlockReason(Unit $unit): ?string
    {
        if ($unit->bookings()->exists()) {
            return 'it has booking history';
        }

        if ($unit->reviews()->exists() || $unit->feedback()->exists()) {
            return 'it has guest reviews or feedback';
        }

        if ($unit->damageReports()->exists()) {
            return 'it has damage reports';
        }

        return null;
    }

    /**
     * @return array{deleted: false, message: string, cleanup_warning: null}
     */
    private function blocked(string $reason): array
    {
        return [
            'deleted' => false,
            'message' => $reason,
            'cleanup_warning' => null,
        ];
    }

    /**
     * @param  array{deleted: true, message: string, photo_paths: list<string>}  $result
     * @return array{deleted: true, message: string, cleanup_warning: ?string}
     */
    private function deleteUnreferencedPhotos(array $result): array
    {
        $failedPaths = [];

        foreach ($result['photo_paths'] as $path) {
            $isReferenced = TransientHouse::query()->where('cover_photo', $path)->exists()
                || UnitPhoto::query()->where('photo_path', $path)->exists();

            if (! $isReferenced && Storage::disk('public')->exists($path)
                && ! Storage::disk('public')->delete($path)) {
                $failedPaths[] = $path;
            }
        }

        return [
            'deleted' => true,
            'message' => $result['message'],
            'cleanup_warning' => $failedPaths === []
                ? null
                : 'Some unreferenced photo files could not be removed: '.implode(', ', $failedPaths),
        ];
    }
}
