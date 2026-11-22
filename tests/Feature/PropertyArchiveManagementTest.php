<?php

use App\Models\Booking;
use App\Models\Invitation;
use App\Models\Report;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitPhoto;
use App\Models\User;
use App\Services\BookingService;
use App\Services\BookingWorkflowService;
use App\Services\PropertyArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function archiveManagementUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::query()->where('name', $role)->firstOrFail()->role_id,
        'first_name' => ucfirst($role),
        'last_name' => 'Archive',
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

function archiveManagementHouse(User $owner, string $name): TransientHouse
{
    return TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => $name,
        'address' => '25 Archive Road',
        'city' => 'Baguio',
        'contact_number' => '5553000',
        'is_active' => true,
    ]);
}

function archiveManagementUnit(TransientHouse $house, string $type): Unit
{
    return Unit::create([
        'house_id' => $house->house_id,
        'unit_type' => $type,
        'capacity' => 2,
        'price' => 1800,
        'status' => 'Available',
    ]);
}

test('archiving and restoring a house preserves visibility settings and independently archived units', function () {
    $owner = archiveManagementUser('Owner', 'archive-owner@example.com');
    $house = archiveManagementHouse($owner, 'Maple House');
    $unitArchivedWithHouse = archiveManagementUnit($house, 'Maple Suite');
    $independentlyArchivedUnit = archiveManagementUnit($house, 'Maple Cabin');
    $archive = app(PropertyArchiveService::class);
    $archive->archiveUnit($house, $independentlyArchivedUnit, $owner);

    $this->actingAs($owner)
        ->get(route('owner.houses.edit', $house))
        ->assertSee('Danger Zone')
        ->assertSee('Archive House');

    $this->post(route('owner.houses.archive', $house))
        ->assertRedirect(route('owner.houses.index'))
        ->assertSessionHas('success');

    expect($house->fresh()->archived_at)->not->toBeNull()
        ->and($house->fresh()->archived_by)->toBe($owner->user_id)
        ->and($house->fresh()->is_active)->toBeTrue()
        ->and($unitArchivedWithHouse->fresh()->archived_at)->not->toBeNull()
        ->and($unitArchivedWithHouse->fresh()->archived_by)->toBe($owner->user_id)
        ->and($unitArchivedWithHouse->fresh()->archived_with_house)->toBeTrue()
        ->and($independentlyArchivedUnit->fresh()->archived_with_house)->toBeFalse();

    $this->get(route('public.transients'))->assertDontSee('Maple House');
    $this->get(route('public.house', $house))->assertNotFound();
    $this->get(route('owner.houses.index'))->assertDontSee('Maple House');
    $this->get(route('owner.houses.archive.index', ['q' => 'Maple', 'from' => today()->toDateString(), 'to' => today()->toDateString()]))
        ->assertOk()
        ->assertSee('Archives')
        ->assertSee('Resource')
        ->assertSee('Archived At')
        ->assertSee('Archived By')
        ->assertSee('Export')
        ->assertSee('Maple House')
        ->assertSee('Maple Suite')
        ->assertSee('archive-bulk-restore', false)
        ->assertSee('Delete Permanently')
        ->assertDontSee('Restore Selected')
        ->assertDontSee('Back to Properties');

    $this->post(route('owner.houses.restore', $house))
        ->assertRedirect(route('owner.houses.index'));

    expect($house->fresh()->archived_at)->toBeNull()
        ->and($unitArchivedWithHouse->fresh()->archived_at)->toBeNull()
        ->and($unitArchivedWithHouse->fresh()->archived_with_house)->toBeFalse()
        ->and($independentlyArchivedUnit->fresh()->archived_at)->not->toBeNull();
});

test('archive records can be filtered by resource and exported as a csv', function () {
    $owner = archiveManagementUser('Owner', 'archive-filter-owner@example.com');
    $independentHouse = archiveManagementHouse($owner, 'Unit Archive Parent');
    $independentUnit = archiveManagementUnit($independentHouse, 'Archived Suite');
    $formulaNamedUnit = archiveManagementUnit($independentHouse, '=2+2');
    $house = archiveManagementHouse($owner, 'House Only Archive');
    $archive = app(PropertyArchiveService::class);
    $archive->archiveUnit($independentHouse, $independentUnit, $owner);
    $archive->archiveUnit($independentHouse, $formulaNamedUnit, $owner);
    $archive->archiveHouse($house, $owner);

    $this->actingAs($owner)
        ->get(route('owner.houses.archive.index', ['resource' => 'house']))
        ->assertOk()
        ->assertSee('House Only Archive')
        ->assertDontSee('Archived Suite');

    $this->get(route('owner.houses.archive.index', ['resource' => 'unit']))
        ->assertOk()
        ->assertSee('Archived Suite')
        ->assertSee('Unit Archive Parent')
        ->assertDontSee('House Only Archive');

    $export = $this->get(route('owner.houses.archive.export', ['resource' => 'unit']))
        ->assertDownload('property-archives.csv')
        ->assertHeaderContains('content-type', 'text/csv');
    ob_start();
    $export->baseResponse->sendContent();
    $csv = ob_get_clean();

    expect($csv)
        ->toContain('Unit,"Archived Suite","Unit Archive Parent",')
        ->and($csv)->toContain("Unit,'=2+2,\"Unit Archive Parent\",");
});

test('unit restore stays disabled with a tooltip until its parent house is restored', function () {
    $owner = archiveManagementUser('Owner', 'disabled-unit-restore-owner@example.com');
    $house = archiveManagementHouse($owner, 'Archived Parent');
    $unit = archiveManagementUnit($house, 'Independently Archived Unit');
    $archive = app(PropertyArchiveService::class);
    $archive->archiveUnit($house, $unit, $owner);
    $archive->archiveHouse($house, $owner);

    $this->actingAs($owner)
        ->get(route('owner.houses.archive.index'))
        ->assertOk()
        ->assertSee('Independently Archived Unit')
        ->assertSee('class="property-archive-disabled-restore" data-tooltip="Restore the house first"', false)
        ->assertSee('disabled aria-label="Restore Independently Archived Unit at Archived Parent; restore the house first"', false);

    $archive->restoreHouse($house);

    $this->get(route('owner.houses.archive.index'))
        ->assertOk()
        ->assertSee('Independently Archived Unit')
        ->assertDontSee('data-tooltip="Restore the house first"', false)
        ->assertSee(route('owner.houses.units.restore', [$house, $unit]), false);
});

test('mixed archive bulk restoration preserves house and unit lifecycle behavior', function () {
    $owner = archiveManagementUser('Owner', 'archive-bulk-restore-owner@example.com');
    $archivedHouse = archiveManagementHouse($owner, 'Archived Parent House');
    $houseUnit = archiveManagementUnit($archivedHouse, 'House Unit');
    $independentHouse = archiveManagementHouse($owner, 'Active Parent House');
    $independentUnit = archiveManagementUnit($independentHouse, 'Independent Unit');
    $archive = app(PropertyArchiveService::class);
    $archive->archiveHouse($archivedHouse, $owner);
    $archive->archiveUnit($independentHouse, $independentUnit, $owner);

    $this->actingAs($owner)
        ->post(route('owner.houses.archive.bulk'), [
            'action' => 'restore',
            'records' => [
                'unit:'.$houseUnit->unit_id,
                'house:'.$archivedHouse->house_id,
                'unit:'.$independentUnit->unit_id,
            ],
        ])
        ->assertRedirect(route('owner.houses.archive.index'))
        ->assertSessionHas('archiveResults', fn (array $results): bool => count($results) === 3);

    expect($archivedHouse->fresh()->archived_at)->toBeNull()
        ->and($houseUnit->fresh()->archived_at)->toBeNull()
        ->and($independentUnit->fresh()->archived_at)->toBeNull();
});

test('mixed archive bulk deletion removes child units before their selected house', function () {
    $owner = archiveManagementUser('Owner', 'archive-bulk-delete-owner@example.com');
    $archivedHouse = archiveManagementHouse($owner, 'Bulk Delete House');
    $houseUnit = archiveManagementUnit($archivedHouse, 'Bulk Delete House Unit');
    $independentHouse = archiveManagementHouse($owner, 'Bulk Delete Unit Parent');
    $independentUnit = archiveManagementUnit($independentHouse, 'Bulk Delete Independent Unit');
    $archive = app(PropertyArchiveService::class);
    $archive->archiveHouse($archivedHouse, $owner);
    $archive->archiveUnit($independentHouse, $independentUnit, $owner);

    $this->actingAs($owner)
        ->post(route('owner.houses.archive.bulk'), [
            'action' => 'delete',
            'records' => [
                'house:'.$archivedHouse->house_id,
                'unit:'.$houseUnit->unit_id,
                'unit:'.$independentUnit->unit_id,
            ],
        ])
        ->assertRedirect(route('owner.houses.archive.index'))
        ->assertSessionHas('archiveResults', fn (array $results): bool => count($results) === 3);

    $this->assertDatabaseMissing('transient_houses', ['house_id' => $archivedHouse->house_id]);
    $this->assertDatabaseMissing('units', ['unit_id' => $houseUnit->unit_id]);
    $this->assertDatabaseMissing('units', ['unit_id' => $independentUnit->unit_id]);
});

test('archiving a unit removes it from public listings and blocks new and pending bookings', function () {
    $this->travelTo(now()->startOfDay());
    $owner = archiveManagementUser('Owner', 'unit-archive-owner@example.com');
    $guest = archiveManagementUser('Guest', 'unit-archive-guest@example.com');
    $house = archiveManagementHouse($owner, 'Cedar House');
    $unit = archiveManagementUnit($house, 'Cedar Room');
    $sibling = archiveManagementUnit($house, 'Cedar Loft');
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => '2027-01-15',
        'check_out' => '2027-01-16',
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => 'Pending',
    ]);

    $this->actingAs($owner)
        ->post(route('owner.houses.units.archive', [$house, $unit]))
        ->assertRedirect(route('owner.houses.units', $house));

    expect($unit->fresh()->archived_at)->not->toBeNull()
        ->and($unit->fresh()->status)->toBe('Available')
        ->and($house->fresh()->archived_at)->toBeNull();

    $this->get(route('public.unit', $unit))->assertNotFound();
    $this->get(route('public.house', $house))
        ->assertOk()
        ->assertDontSee('Cedar Room')
        ->assertSee('Cedar Loft');
    $this->get(route('owner.houses.archive.index'))
        ->assertOk()
        ->assertSee('Cedar Room');

    expect(fn () => app(BookingService::class)->createBooking($guest, $unit, [
        'check_in' => '2027-02-01',
        'check_out' => '2027-02-02',
        'adult_count' => 1,
        'child_count' => 0,
        'guests' => [['full_name' => 'Archive Guest']],
    ]))->toThrow(ValidationException::class, 'This unit is no longer available for booking.');

    expect(fn () => app(BookingWorkflowService::class)->accept($owner, $booking->booking_id))
        ->toThrow(ValidationException::class, 'Archived houses or units cannot accept booking requests.');
});

test('permanent house deletion keeps booking history and its photos intact', function () {
    Storage::fake('public');
    $owner = archiveManagementUser('Owner', 'protected-house-owner@example.com');
    $guest = archiveManagementUser('Guest', 'protected-house-guest@example.com');
    $house = archiveManagementHouse($owner, 'Protected House');
    $unit = archiveManagementUnit($house, 'Protected Room');
    $house->update(['cover_photo' => 'houses/protected.jpg']);
    Storage::disk('public')->put('houses/protected.jpg', 'cover');
    $booking = Booking::create([
        'user_id' => $guest->user_id,
        'unit_id' => $unit->unit_id,
        'check_in' => '2028-01-15',
        'check_out' => '2028-01-16',
        'adult_count' => 1,
        'child_count' => 0,
        'total_price' => 1800,
        'status' => 'Cancelled',
    ]);
    app(PropertyArchiveService::class)->archiveHouse($house, $owner);

    $this->actingAs($owner)
        ->delete(route('owner.houses.permanently-delete', $house))
        ->assertRedirect(route('owner.houses.archive.index'))
        ->assertSessionHas('archiveResults', fn (array $results): bool => str_contains($results[0], 'booking history'));

    $this->assertDatabaseHas('transient_houses', ['house_id' => $house->house_id]);
    $this->assertDatabaseHas('units', ['unit_id' => $unit->unit_id]);
    $this->assertDatabaseHas('bookings', ['booking_id' => $booking->booking_id]);
    Storage::disk('public')->assertExists('houses/protected.jpg');
});

test('permanent unit deletion removes only unreferenced photos and reports protected house dependencies', function () {
    Storage::fake('public');
    $owner = archiveManagementUser('Owner', 'safe-delete-owner@example.com');
    $house = archiveManagementHouse($owner, 'Safe Delete House');
    $firstUnit = archiveManagementUnit($house, 'First Room');
    $secondUnit = archiveManagementUnit($house, 'Second Room');
    Storage::disk('public')->put('units/shared.jpg', 'photo');
    UnitPhoto::create(['unit_id' => $firstUnit->unit_id, 'photo_path' => 'units/shared.jpg', 'is_cover' => true, 'sort_order' => 0]);
    UnitPhoto::create(['unit_id' => $secondUnit->unit_id, 'photo_path' => 'units/shared.jpg', 'is_cover' => true, 'sort_order' => 0]);
    $archive = app(PropertyArchiveService::class);
    $archive->archiveUnit($house, $firstUnit, $owner);
    $archive->archiveUnit($house, $secondUnit, $owner);

    $this->actingAs($owner)
        ->delete(route('owner.houses.units.permanently-delete', [$house, $firstUnit]))
        ->assertSessionHas('archiveResults');

    $this->assertDatabaseMissing('units', ['unit_id' => $firstUnit->unit_id]);
    Storage::disk('public')->assertExists('units/shared.jpg');

    $this->delete(route('owner.houses.units.permanently-delete', [$house, $secondUnit]));
    $this->assertDatabaseMissing('units', ['unit_id' => $secondUnit->unit_id]);
    Storage::disk('public')->assertMissing('units/shared.jpg');

    $reportHouse = archiveManagementHouse($owner, 'Reported House');
    $invitedHouse = archiveManagementHouse($owner, 'Invited House');
    $archive->archiveHouse($reportHouse, $owner);
    $archive->archiveHouse($invitedHouse, $owner);
    Report::create([
        'house_id' => $reportHouse->house_id,
        'generated_by' => $owner->user_id,
        'report_type' => 'archive test',
        'date_from' => '2026-01-01',
        'date_to' => '2026-01-31',
    ]);
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $invitedHouse->house_id,
        'role_id' => Role::query()->where('name', 'Staff')->firstOrFail()->role_id,
        'email' => 'invited-staff@example.com',
        'token' => 'archive-protection-token',
        'expires_at' => now()->addDay(),
    ]);

    $reportResult = $archive->permanentlyDeleteHouse($reportHouse);
    $invitationResult = $archive->permanentlyDeleteHouse($invitedHouse);

    expect($reportResult['deleted'])->toBeFalse()
        ->and($reportResult['message'])->toContain('reports')
        ->and($invitationResult['deleted'])->toBeFalse()
        ->and($invitationResult['message'])->toContain('staff invitations');

    $this->actingAs($owner)
        ->post(route('owner.houses.archive.houses'), [
            'action' => 'delete',
            'house_ids' => [$reportHouse->house_id, $invitedHouse->house_id],
        ])
        ->assertRedirect(route('owner.houses.archive.index'))
        ->assertSessionHas('archiveResults', fn (array $results): bool => count($results) === 2
            && str_contains($results[0], 'reports')
            && str_contains($results[1], 'staff invitations'));

    $this->assertDatabaseHas('transient_houses', ['house_id' => $reportHouse->house_id]);
    $this->assertDatabaseHas('transient_houses', ['house_id' => $invitedHouse->house_id]);
});

test('archive actions cannot expose or change another owner property', function () {
    $owner = archiveManagementUser('Owner', 'archive-scope-owner@example.com');
    $otherOwner = archiveManagementUser('Owner', 'archive-scope-other@example.com');
    $otherHouse = archiveManagementHouse($otherOwner, 'Other Owner House');
    app(PropertyArchiveService::class)->archiveHouse($otherHouse, $otherOwner);

    $this->actingAs($owner)
        ->get(route('owner.houses.archive.index'))
        ->assertDontSee('Other Owner House');

    $this->post(route('owner.houses.restore', $otherHouse))->assertNotFound();
    $this->post(route('owner.houses.archive.houses'), [
        'action' => 'delete',
        'house_ids' => [$otherHouse->house_id],
    ])->assertNotFound();
    $this->post(route('owner.houses.archive.bulk'), [
        'action' => 'restore',
        'records' => ['house:'.$otherHouse->house_id],
    ])->assertNotFound();

    expect($otherHouse->fresh()->archived_at)->not->toBeNull();
});

test('assigned staff can manage archived houses and property names remain escaped', function () {
    $owner = archiveManagementUser('Owner', 'staff-archive-owner@example.com');
    $staff = archiveManagementUser('Staff', 'assigned-archive-staff@example.com');
    $house = archiveManagementHouse($owner, '<script>alert(1)</script>');
    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $house->house_id,
        'role_id' => Role::query()->where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'assigned-archive-token',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);
    app(PropertyArchiveService::class)->archiveHouse($house, $owner);

    $this->actingAs($staff)
        ->get(route('staff.houses.archive.index'))
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false);

    $this->post(route('staff.houses.restore', $house))
        ->assertRedirect(route('staff.houses.index'));

    expect($house->fresh()->archived_at)->toBeNull();
});
