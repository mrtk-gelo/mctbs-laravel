<?php

use App\Models\Invitation;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitAddon;
use App\Models\UnitDamageItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createHouseManagementUser(string $role, string $email): User
{
    return User::create([
        'role_id' => Role::where('name', $role)->firstOrFail()->role_id,
        'first_name' => 'Test',
        'last_name' => ucfirst($role),
        'email' => $email,
        'password' => 'Password123!',
        'is_active' => true,
    ]);
}

function createManagedHouse(User $owner, string $name): TransientHouse
{
    return TransientHouse::create([
        'owner_id' => $owner->user_id,
        'name' => $name,
        'address' => '10 Test Street',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'is_active' => true,
    ]);
}

function createUnitPhotoUpload(string $name): UploadedFile
{
    $imageContents = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC',
        true
    );

    return UploadedFile::fake()->createWithContent($name, $imageContents);
}

function createLargeImageUpload(string $name): UploadedFile
{
    return UploadedFile::fake()->image($name)->size(6 * 1024);
}

test('owner creates a house and unit that appear on public transient pages', function () {
    $owner = createHouseManagementUser('Owner', 'house-manager@example.com');

    $this->actingAs($owner)
        ->get('/owner/dashboard')
        ->assertSee('Transient Houses');

    $this->get(route('owner.houses.create'))
        ->assertSee('Add Transient House')
        ->assertSee('Add Policy')
        ->assertSee('name="policies[0]"', false)
        ->assertSee('class="social-link-url"', false);

    $this->post(route('owner.houses.store'), [
        'name' => 'Pine Retreat',
        'address' => '10 Test Street',
        'description' => 'A quiet city retreat.',
        'city' => 'Baguio',
        'barangay' => 'Camp John Hay',
        'contact_number' => '5551000',
        'policies' => ['No smoking.', 'Quiet hours start at 10 PM.'],
        'social_links' => [
            ['name' => 'Facebook', 'url' => 'https://facebook.com/pine-retreat'],
            ['name' => 'Messenger', 'url' => 'https://m.me/pine-retreat'],
        ],
        'owner_id' => 999,
        'is_active' => false,
    ])->assertRedirect(route('owner.houses.index'))
        ->assertSessionHas('success');

    $house = TransientHouse::where('name', 'Pine Retreat')->firstOrFail();
    expect($house->owner_id)->toBe($owner->user_id)
        ->and($house->is_active)->toBeTrue()
        ->and($house->policies)->toBe("No smoking.\nQuiet hours start at 10 PM.")
        ->and($house->social_links)->toBe([
            ['name' => 'Facebook', 'url' => 'https://facebook.com/pine-retreat'],
            ['name' => 'Messenger', 'url' => 'https://m.me/pine-retreat'],
        ]);

    $this->get(route('owner.houses.index'))
        ->assertSee('Pine Retreat')
        ->assertSee('10 Test Street, Camp John Hay, Baguio')
        ->assertSee('Manage Units');
    $this->get(route('owner.houses.units', $house))
        ->assertSee('Pine Retreat')
        ->assertSee('Add Unit');
    $this->get(route('owner.houses.units.create', $house))
        ->assertSee('Add-on Amenities')
        ->assertSee('Damage Policies')
        ->assertSee('class="form-row unit-time-fields"', false)
        ->assertSee('data-add-unit-item', false);

    $this->post(route('owner.houses.units.store', $house), [
        'unit_type' => 'Queen Studio',
        'capacity' => 3,
        'maximum_capacity' => 5,
        'extra_guest_fee' => '200.00',
        'price' => '1250.00',
        'status' => 'Available',
        'child_free_through_age' => 3,
        'description' => 'Studio with queen bed.',
        'addons' => [
            ['name' => 'Extra bed', 'price' => '500.00'],
            ['name' => 'Karaoke', 'price' => '800.00'],
        ],
        'damage_items' => [
            ['name' => 'Electric stove', 'price' => '3500.00'],
        ],
        'house_id' => 999,
    ])->assertRedirect(route('owner.houses.units', $house))
        ->assertSessionHas('success');

    $unit = Unit::where('unit_type', 'Queen Studio')->firstOrFail();
    expect($unit->house_id)->toBe($house->house_id)
        ->and($unit->capacity)->toBe(3)
        ->and($unit->maximum_capacity)->toBe(5)
        ->and($unit->check_in_time)->toBe('14:00')
        ->and($unit->check_out_time)->toBe('12:00')
        ->and($unit->pricing_mode)->toBe('Per Unit')
        ->and($unit->extra_guest_fee)->toBe('200.00')
        ->and($unit->child_free_through_age)->toBe(3)
        ->and($unit->price)->toBe('1250.00')
        ->and($unit->addons()->orderBy('unit_addon_id')->pluck('name')->all())->toBe(['Extra bed', 'Karaoke'])
        ->and($unit->damageItems()->pluck('name')->all())->toBe(['Electric stove']);
    $this->get(route('owner.houses.units', $house))
        ->assertSee('Queen Studio')
        ->assertSee('1,250.00');
    $this->get(route('owner.houses.units.edit', [$house, $unit]))
        ->assertSee('class="form-row unit-time-fields"', false)
        ->assertSee('name="check_in_time"', false)
        ->assertSee('name="check_out_time"', false)
        ->assertSee('name="pricing_mode"', false)
        ->assertSee('Price per paying head per night');
    $this->get(route('public.transients'))
        ->assertSee('Pine Retreat')
        ->assertSee('1 unit');
    $this->get(route('public.house', $house))
        ->assertSee('Pine Retreat')
        ->assertSee('House Policies')
        ->assertSee('No smoking.')
        ->assertSee('Quiet hours start at 10 PM.')
        ->assertSee('Queen Studio')
        ->assertSee('Capacity 3 standard; up to 5 counted guests')
        ->assertSee('Ages 3 and under are free and excluded')
        ->assertSee('₱1,250.00/night');
    $this->get(route('public.house', $house))
        ->assertSee('href="https://facebook.com/pine-retreat"', false)
        ->assertSee('href="https://m.me/pine-retreat"', false);
    $this->get(route('public.unit', $unit))
        ->assertSee('Queen Studio')
        ->assertSee('Capacity')
        ->assertDontSee('Rental packages')
        ->assertSee('₱1,250.00 / night')
        ->assertSee('.cal-cell.past { background: #e5e7eb;', false)
        ->assertDontSee('global-back-button', false);
});

test('saved house policies load into separate edit inputs and display as public cards', function () {
    $owner = createHouseManagementUser('Owner', 'house-policy-owner@example.com');
    $house = createManagedHouse($owner, 'House Policy Property');
    $house->update(['policies' => "No smoking.\r\nKeep the gate closed."]);

    $this->actingAs($owner)
        ->get(route('owner.houses.edit', $house))
        ->assertSee('name="policies[0]"', false)
        ->assertSee('value="No smoking."', false)
        ->assertSee('name="policies[1]"', false)
        ->assertSee('value="Keep the gate closed."', false);

    $this->get(route('public.house', $house))
        ->assertSee('class="house-policy-card"', false)
        ->assertSee('No smoking.')
        ->assertSee('Keep the gate closed.');

    $this->put(route('owner.houses.update', $house), [
        'name' => 'House Policy Property',
        'address' => '10 Test Street',
        'city' => 'Baguio',
        'contact_number' => '5551000',
        'policies' => ['No smoking.', '', 'Quiet hours start at 9 PM.'],
        'social_links' => [],
    ])->assertRedirect(route('owner.houses.index'));

    expect($house->fresh()->policies)->toBe("No smoking.\nQuiet hours start at 9 PM.");
});

test('unit pricing mode and times can be created and updated', function () {
    $owner = createHouseManagementUser('Owner', 'unit-pricing-owner@example.com');
    $house = createManagedHouse($owner, 'Unit Pricing House');

    $this->actingAs($owner)
        ->post(route('owner.houses.units.store', $house), [
            'unit_type' => 'Per Head Room',
            'capacity' => 2,
            'maximum_capacity' => 4,
            'check_in_time' => '15:00',
            'check_out_time' => '11:00',
            'pricing_mode' => 'Per Head',
            'price' => '600.00',
            'status' => 'Available',
        ])
        ->assertRedirect(route('owner.houses.units', $house));

    $unit = Unit::where('unit_type', 'Per Head Room')->firstOrFail();
    expect($unit->pricing_mode)->toBe('Per Head')
        ->and($unit->price)->toBe('600.00')
        ->and($unit->check_in_time)->toBe('15:00')
        ->and($unit->check_out_time)->toBe('11:00');

    $this->put(route('owner.houses.units.update', [$house, $unit]), [
        'unit_type' => 'Per Head Room',
        'capacity' => 2,
        'maximum_capacity' => 4,
        'check_in_time' => '16:00',
        'check_out_time' => '10:00',
        'pricing_mode' => 'Per Unit',
        'extra_guest_fee' => '150.00',
        'price' => '1800.00',
        'status' => 'Available',
    ])->assertRedirect(route('owner.houses.units', $house));

    expect($unit->fresh()->pricing_mode)->toBe('Per Unit')
        ->and($unit->fresh()->price)->toBe('1800.00')
        ->and($unit->fresh()->check_in_time)->toBe('16:00')
        ->and($unit->fresh()->check_out_time)->toBe('10:00');
});

test('unit creation rejects zero and negative prices', function () {
    $owner = createHouseManagementUser('Owner', 'unit-price-validation-owner@example.com');
    $house = createManagedHouse($owner, 'Unit Price Validation House');

    foreach (['0.00', '-1.00'] as $index => $price) {
        $unitType = 'Invalid Price Unit '.$index;

        $this->actingAs($owner)
            ->from(route('owner.houses.units.create', $house))
            ->post(route('owner.houses.units.store', $house), [
                'unit_type' => $unitType,
                'capacity' => 2,
                'price' => $price,
                'status' => 'Available',
            ])
            ->assertRedirect(route('owner.houses.units.create', $house))
            ->assertSessionHasErrors('price');

        $this->assertDatabaseMissing('units', ['unit_type' => $unitType]);
    }
});

test('unit updating rejects zero and negative prices', function () {
    $owner = createHouseManagementUser('Owner', 'unit-price-update-owner@example.com');
    $house = createManagedHouse($owner, 'Unit Price Update House');
    $unit = $house->units()->create([
        'unit_type' => 'Valid Price Unit',
        'capacity' => 2,
        'price' => '1000.00',
        'status' => 'Available',
    ]);

    foreach (['0.00', '-1.00'] as $price) {
        $this->actingAs($owner)
            ->from(route('owner.houses.units', $house))
            ->put(route('owner.houses.units.update', [$house, $unit]), [
                'unit_type' => $unit->unit_type,
                'capacity' => 2,
                'price' => $price,
                'status' => 'Available',
            ])
            ->assertRedirect(route('owner.houses.units', $house))
            ->assertSessionHasErrors('price');

        expect($unit->fresh()->price)->toBe('1000.00');
    }
});

test('unit creation requires at least two hours between check-out and check-in', function () {
    $owner = createHouseManagementUser('Owner', 'unit-time-owner@example.com');
    $house = createManagedHouse($owner, 'Unit Time House');
    $cases = [
        ['12:00', '14:00', true],
        ['12:00', '13:59', false],
        ['12:00', '12:00', false],
        ['10:00', '12:00', true],
    ];

    foreach ($cases as $index => [$checkOutTime, $checkInTime, $isValid]) {
        $unitType = 'Turnover Creation Unit '.$index;
        $response = $this->actingAs($owner)
            ->from(route('owner.houses.units.create', $house))
            ->post(route('owner.houses.units.store', $house), [
                'unit_type' => $unitType,
                'capacity' => 2,
                'maximum_capacity' => 2,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'pricing_mode' => 'Per Unit',
                'extra_guest_fee' => '0.00',
                'price' => '1800.00',
                'status' => 'Available',
            ]);

        if ($isValid) {
            $response->assertRedirect(route('owner.houses.units', $house));
            $this->assertDatabaseHas('units', [
                'unit_type' => $unitType,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
            ]);

            continue;
        }

        $response->assertRedirect(route('owner.houses.units.create', $house))
            ->assertSessionHasErrors([
                'check_out_time' => 'Check-in time must be at least two hours after check-out time.',
            ]);
        $this->assertDatabaseMissing('units', ['unit_type' => $unitType]);
    }
});

test('unit updates require at least two hours between check-out and check-in', function () {
    $owner = createHouseManagementUser('Owner', 'unit-update-time-owner@example.com');
    $house = createManagedHouse($owner, 'Unit Update Time House');
    $cases = [
        ['12:00', '14:00', true],
        ['12:00', '13:59', false],
        ['12:00', '12:00', false],
        ['10:00', '12:00', true],
    ];

    foreach ($cases as $index => [$checkOutTime, $checkInTime, $isValid]) {
        $unit = $house->units()->create([
            'unit_type' => 'Turnover Update Unit '.$index,
            'capacity' => 2,
            'maximum_capacity' => 2,
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'pricing_mode' => 'Per Unit',
            'price' => '1800.00',
            'status' => 'Available',
        ]);
        $response = $this->actingAs($owner)
            ->from(route('owner.houses.units', $house))
            ->put(route('owner.houses.units.update', [$house, $unit]), [
                'unit_type' => $unit->unit_type,
                'capacity' => 2,
                'maximum_capacity' => 2,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'pricing_mode' => 'Per Unit',
                'extra_guest_fee' => '0.00',
                'price' => '1800.00',
                'status' => 'Available',
            ]);

        if ($isValid) {
            $response->assertRedirect(route('owner.houses.units', $house));
            expect($unit->fresh()->check_in_time)->toBe($checkInTime)
                ->and($unit->fresh()->check_out_time)->toBe($checkOutTime);

            continue;
        }

        $response->assertRedirect(route('owner.houses.units', $house))
            ->assertSessionHasErrors([
                'check_out_time' => 'Check-in time must be at least two hours after check-out time.',
            ]);
        expect($unit->fresh()->check_in_time)->toBe('14:00')
            ->and($unit->fresh()->check_out_time)->toBe('12:00');
    }
});

test('public house contact wraps long host email addresses', function () {
    $email = 'contact.the.host.for.booking.enquiries.with.a.long.address@very-long-property-management-company-example.com';
    $owner = createHouseManagementUser('Owner', $email);
    $house = createManagedHouse($owner, 'Long Email Retreat');

    $this->get(route('public.house', $house))
        ->assertSee('href="mailto:'.$email.'"', false)
        ->assertSee('class="contact-email"', false)
        ->assertSee('overflow-wrap: anywhere', false)
        ->assertSee($email);
});

test('owner can edit or remove unit add-ons and damage policies', function () {
    $owner = createHouseManagementUser('Owner', 'unit-items-edit-owner@example.com');
    $house = createManagedHouse($owner, 'Unit Items House');
    $unit = $house->units()->create([
        'unit_type' => 'Garden Suite',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
    ]);
    $unit->addons()->createMany([
        ['name' => 'Extra bed', 'price' => 500],
        ['name' => 'Karaoke', 'price' => 800],
    ]);
    $unit->damageItems()->create([
        'name' => 'Electric stove',
        'price' => 3500,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.houses.units.edit', [$house, $unit]))
        ->assertSee('Add-on Amenities')
        ->assertSee('Damage Policies')
        ->assertSee('value="Extra bed"', false)
        ->assertSee('value="500.00"', false)
        ->assertSee('value="Electric stove"', false)
        ->assertSee('value="3500.00"', false);

    $this->put(route('owner.houses.units.update', [$house, $unit]), [
        'unit_type' => 'Garden Suite',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
        'addons' => [
            ['name' => 'Updated bed', 'price' => '0.00'],
        ],
        'damage_items' => [],
    ])->assertRedirect(route('owner.houses.units', $house))
        ->assertSessionHas('success');

    expect($unit->fresh()->addons()->pluck('name')->all())->toBe(['Updated bed'])
        ->and($unit->fresh()->addons()->first()->price)->toBe('0.00')
        ->and($unit->fresh()->damageItems()->count())->toBe(0);
});

test('unit add-on and damage rows require names and non-negative numeric prices', function () {
    $owner = createHouseManagementUser('Owner', 'unit-items-validation-owner@example.com');
    $house = createManagedHouse($owner, 'Unit Items Validation House');

    $this->actingAs($owner)
        ->post(route('owner.houses.units.store', $house), [
            'unit_type' => 'Garden Suite',
            'capacity' => 2,
            'price' => 1200,
            'status' => 'Available',
            'addons' => [
                ['name' => '', 'price' => '-1'],
            ],
            'damage_items' => [
                ['name' => 'Electric stove', 'price' => 'not-a-number'],
            ],
        ])
        ->assertSessionHasErrors([
            'addons.0.name',
            'addons.0.price',
            'damage_items.0.price',
        ]);

    expect(Unit::query()->count())->toBe(0)
        ->and(UnitAddon::query()->count())->toBe(0)
        ->and(UnitDamageItem::query()->count())->toBe(0);
});

test('public transient search returns matching results over ajax', function () {
    $owner = createHouseManagementUser('Owner', 'ajax-search-owner@example.com');
    createManagedHouse($owner, 'Ajax Search Cottage');
    createManagedHouse($owner, 'Different Mountain Lodge');

    $this->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get(route('public.transients', ['q' => 'Ajax Search']))
        ->assertOk()
        ->assertSee('Ajax Search Cottage')
        ->assertDontSee('Different Mountain Lodge')
        ->assertDontSee('<x-shell', false);
});

test('owner uploads multiple unit photos and guests can browse the gallery', function () {
    Storage::fake('public');
    $owner = createHouseManagementUser('Owner', 'unit-gallery-owner@example.com');
    $house = createManagedHouse($owner, 'Gallery House');

    $this->actingAs($owner)
        ->get(route('owner.houses.units.create', $house))
        ->assertSee('name="photos[]"', false)
        ->assertSee('multiple', false);

    $this->post(route('owner.houses.units.store', $house), [
        'unit_type' => 'Garden Suite',
        'capacity' => 2,
        'price' => 1500,
        'status' => 'Available',
        'photos' => [
            createUnitPhotoUpload('suite-main.png'),
            createUnitPhotoUpload('suite-bedroom.png'),
        ],
    ])->assertRedirect(route('owner.houses.units', $house))
        ->assertSessionHas('success');

    $unit = Unit::where('unit_type', 'Garden Suite')->firstOrFail();
    $photos = $unit->photos;

    expect($photos)->toHaveCount(2)
        ->and($photos[0]->is_cover)->toBeTrue()
        ->and($photos[1]->is_cover)->toBeFalse()
        ->and($photos[0]->sort_order)->toBe(0)
        ->and($photos[1]->sort_order)->toBe(1);

    foreach ($photos as $photo) {
        Storage::disk('public')->assertExists($photo->photo_path);
    }

    $this->get(route('owner.houses.units', $house))
        ->assertSee(asset('storage/'.$photos[0]->photo_path), false)
        ->assertSee('2 photos');

    $this->get(route('public.house', $house))
        ->assertSee(asset('storage/'.$photos[0]->photo_path), false);

    $this->get(route('public.unit', $unit))
        ->assertSee(asset('storage/'.$photos[0]->photo_path), false)
        ->assertSee(asset('storage/'.$photos[1]->photo_path), false)
        ->assertSee('View photo 2')
        ->assertSee('data-gallery-index="2"', false);
});

test('owner can create a house with a cover photo larger than 5 MB', function () {
    Storage::fake('public');
    $owner = createHouseManagementUser('Owner', 'large-cover-create-owner@example.com');

    $this->actingAs($owner)
        ->post(route('owner.houses.store'), [
            'name' => 'Large Cover House',
            'address' => '10 Test Street',
            'city' => 'Baguio',
            'contact_number' => '5551000',
            'cover_photo' => createLargeImageUpload('large-cover.png'),
        ])
        ->assertRedirect(route('owner.houses.index'))
        ->assertSessionHas('success');

    $house = TransientHouse::where('name', 'Large Cover House')->firstOrFail();
    expect($house->cover_photo)->not->toBeEmpty();
    Storage::disk('public')->assertExists($house->cover_photo);
});

test('owner can update a house with a cover photo larger than 5 MB', function () {
    Storage::fake('public');
    $owner = createHouseManagementUser('Owner', 'large-cover-update-owner@example.com');
    $house = createManagedHouse($owner, 'Large Cover Update House');

    $this->actingAs($owner)
        ->put(route('owner.houses.update', $house), [
            'name' => $house->name,
            'address' => $house->address,
            'city' => $house->city,
            'contact_number' => $house->contact_number,
            'cover_photo' => createLargeImageUpload('large-cover.png'),
        ])
        ->assertRedirect(route('owner.houses.index'))
        ->assertSessionHas('success');

    expect($house->fresh()->cover_photo)->not->toBeEmpty();
    Storage::disk('public')->assertExists($house->fresh()->cover_photo);
});

test('owner can create a unit with photos larger than 5 MB', function () {
    Storage::fake('public');
    $owner = createHouseManagementUser('Owner', 'large-unit-photo-create-owner@example.com');
    $house = createManagedHouse($owner, 'Large Unit Photo House');

    $this->actingAs($owner)
        ->post(route('owner.houses.units.store', $house), [
            'unit_type' => 'Large Photo Suite',
            'capacity' => 2,
            'price' => 1200,
            'status' => 'Available',
            'photos' => [createLargeImageUpload('large-unit-photo.png')],
        ])
        ->assertRedirect(route('owner.houses.units', $house))
        ->assertSessionHas('success');

    $unit = Unit::where('unit_type', 'Large Photo Suite')->firstOrFail();
    $photo = $unit->photos()->firstOrFail();
    Storage::disk('public')->assertExists($photo->photo_path);
});

test('owner can update a unit with photos larger than 5 MB', function () {
    Storage::fake('public');
    $owner = createHouseManagementUser('Owner', 'large-unit-photo-update-owner@example.com');
    $house = createManagedHouse($owner, 'Large Unit Photo Update House');
    $unit = $house->units()->create([
        'unit_type' => 'Large Photo Suite',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
    ]);

    $this->actingAs($owner)
        ->put(route('owner.houses.units.update', [$house, $unit]), [
            'unit_type' => $unit->unit_type,
            'capacity' => $unit->capacity,
            'price' => $unit->price,
            'status' => $unit->status,
            'photos' => [createLargeImageUpload('large-unit-photo.png')],
        ])
        ->assertRedirect(route('owner.houses.units', $house))
        ->assertSessionHas('success');

    $photo = $unit->fresh()->photos()->firstOrFail();
    Storage::disk('public')->assertExists($photo->photo_path);
});

test('owner can remove and replace unit photos without allowing cross-unit photo deletion', function () {
    Storage::fake('public');
    $owner = createHouseManagementUser('Owner', 'unit-gallery-edit-owner@example.com');
    $house = createManagedHouse($owner, 'Photo Edit House');
    $unit = $house->units()->create([
        'unit_type' => 'Photo Suite',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
    ]);
    $otherUnit = $house->units()->create([
        'unit_type' => 'Other Suite',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
    ]);
    $firstPhoto = $unit->photos()->create([
        'photo_path' => createUnitPhotoUpload('first.png')->store('units/'.$unit->unit_id, 'public'),
        'is_cover' => true,
        'sort_order' => 0,
    ]);
    $secondPhoto = $unit->photos()->create([
        'photo_path' => createUnitPhotoUpload('second.png')->store('units/'.$unit->unit_id, 'public'),
        'is_cover' => false,
        'sort_order' => 1,
    ]);
    $otherPhoto = $otherUnit->photos()->create([
        'photo_path' => createUnitPhotoUpload('private.png')->store('units/'.$otherUnit->unit_id, 'public'),
        'is_cover' => true,
        'sort_order' => 0,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.houses.units.edit', [$house, $unit]))
        ->assertSee('Current Unit Photos')
        ->assertSee('Main photo')
        ->assertSee('name="remove_photos[]"', false);

    $this->put(route('owner.houses.units.update', [$house, $unit]), [
        'unit_type' => 'Photo Suite',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
        'remove_photos' => [$otherPhoto->photo_id],
    ])->assertNotFound();

    $this->put(route('owner.houses.units.update', [$house, $unit]), [
        'unit_type' => 'Photo Suite',
        'capacity' => 2,
        'price' => 1200,
        'status' => 'Available',
        'remove_photos' => [$firstPhoto->photo_id],
        'photos' => [createUnitPhotoUpload('replacement.png')],
    ])->assertRedirect(route('owner.houses.units', $house))
        ->assertSessionHas('success');

    $photos = $unit->fresh()->photos;
    expect($photos)->toHaveCount(2)
        ->and($photos->first()->photo_id)->toBe($secondPhoto->photo_id)
        ->and($photos->first()->is_cover)->toBeTrue();

    Storage::disk('public')->assertMissing($firstPhoto->photo_path);
    Storage::disk('public')->assertExists($secondPhoto->photo_path);
    Storage::disk('public')->assertExists($otherPhoto->photo_path);
});

test('owner cannot view or add units to another owners house', function () {
    $owner = createHouseManagementUser('Owner', 'owner-one@example.com');
    $otherOwner = createHouseManagementUser('Owner', 'owner-two@example.com');
    $otherHouse = createManagedHouse($otherOwner, 'Private House');

    $this->actingAs($owner)
        ->get(route('owner.houses.index'))
        ->assertDontSee('Private House');
    $this->get(route('owner.houses.edit', $otherHouse))->assertNotFound();
    $this->put(route('owner.houses.update', $otherHouse), [
        'name' => 'Changed by another owner',
        'address' => '10 Test Street',
        'city' => 'Baguio',
        'contact_number' => '5551000',
    ])->assertNotFound();
    $this->get(route('owner.houses.units', $otherHouse))->assertNotFound();
    $this->post(route('owner.houses.units.store', $otherHouse), [
        'unit_type' => 'Unauthorized Unit',
        'capacity' => 2,
        'price' => 500,
        'status' => 'Available',
    ])->assertNotFound();

    $this->assertDatabaseMissing('units', ['unit_type' => 'Unauthorized Unit']);
});

test('house and unit management lists render editable vertical cards with the house cover photo', function () {
    $owner = createHouseManagementUser('Owner', 'card-owner@example.com');
    $house = createManagedHouse($owner, 'Pine Retreat');
    $house->update(['cover_photo' => 'houses/pine-retreat.jpg']);
    $house->units()->create([
        'unit_type' => 'Garden Suite',
        'capacity' => 2,
        'price' => 1500,
        'status' => 'Available',
        'description' => 'A quiet suite beside the garden.',
    ]);

    $this->actingAs($owner)
        ->get(route('owner.houses.index'))
        ->assertSee('management-property-card', false)
        ->assertSee('class="management-property-list"', false)
        ->assertSee('class="unit-card-img"', false)
        ->assertSee(asset('storage/houses/pine-retreat.jpg'), false)
        ->assertSee('alt="Pine Retreat cover photo"', false)
        ->assertSee('Edit', false)
        ->assertSee('Hide house from public site', false)
        ->assertSee('10 Test Street, Baguio')
        ->assertSee('Public');

    $this->get(route('owner.houses.units', $house))
        ->assertSee('management-property-card', false)
        ->assertSee('class="unit-card-img"', false)
        ->assertSee(asset('storage/houses/pine-retreat.jpg'), false)
        ->assertSee('alt="Garden Suite at Pine Retreat"', false)
        ->assertSee('A quiet suite beside the garden.')
        ->assertSee('Edit Unit')
        ->assertSee('Hide unit from public site')
        ->assertDontSee('View Public Page');
});

test('owner can edit house and unit details and control their public availability', function () {
    $owner = createHouseManagementUser('Owner', 'edit-house-owner@example.com');
    $house = createManagedHouse($owner, 'Old House Name');
    $unit = $house->units()->create([
        'unit_type' => 'Old Unit Name',
        'capacity' => 2,
        'price' => 1000,
        'status' => 'Available',
        'description' => 'Old unit description.',
    ]);

    $this->actingAs($owner)
        ->get(route('owner.houses.edit', $house))
        ->assertSee('Edit Transient House')
        ->assertSee('Old House Name');

    $this->put(route('owner.houses.update', $house), [
        'name' => 'Updated House',
        'address' => '25 New Road',
        'city' => 'Baguio',
        'barangay' => 'New Barangay',
        'contact_number' => '5552000',
        'description' => 'Updated house description.',
        'policies' => ['Quiet hours.'],
    ])->assertRedirect(route('owner.houses.index'))
        ->assertSessionHas('success');

    expect($house->fresh()->address)->toBe('25 New Road')
        ->and($house->fresh()->owner_id)->toBe($owner->user_id);

    $this->get(route('owner.houses.units.edit', [$house, $unit]))
        ->assertSee('Edit Unit')
        ->assertSee('Old Unit Name');

    $this->put(route('owner.houses.units.update', [$house, $unit]), [
        'unit_type' => 'Updated Unit',
        'capacity' => 4,
        'price' => 1800,
        'status' => 'Unavailable',
        'description' => 'Updated unit description.',
    ])->assertRedirect(route('owner.houses.units', $house))
        ->assertSessionHas('success');

    expect($unit->fresh()->status)->toBe('Unavailable')
        ->and($unit->fresh()->unit_type)->toBe('Updated Unit');
    $this->get(route('public.transients'))
        ->assertSee('Updated House')
        ->assertSee('0 units');
    $this->get(route('public.house', $house))
        ->assertDontSee('Updated Unit');
    $this->get(route('public.unit', $unit))->assertNotFound();
});

test('owner can publish and hide a house and its unit without changing the schema status fields', function () {
    $owner = createHouseManagementUser('Owner', 'availability-owner@example.com');
    $house = createManagedHouse($owner, 'Visibility House');
    $unit = $house->units()->create([
        'unit_type' => 'Visibility Suite',
        'capacity' => 2,
        'price' => 900,
        'status' => 'Available',
    ]);

    $this->actingAs($owner)
        ->put(route('owner.houses.availability', $house), ['is_active' => '0'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($house->fresh()->is_active)->toBeFalse();
    $this->get(route('public.transients'))->assertDontSee('Visibility House');
    $this->get(route('public.house', $house))->assertNotFound();

    $this->put(route('owner.houses.availability', $house), ['is_active' => '1'])
        ->assertRedirect()
        ->assertSessionHas('success');
    $this->put(route('owner.houses.units.availability', [$house, $unit]), ['status' => 'Unavailable'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($house->fresh()->is_active)->toBeTrue()
        ->and($unit->fresh()->status)->toBe('Unavailable');
    $this->get(route('public.house', $house))->assertDontSee('Visibility Suite');

    $this->put(route('owner.houses.units.availability', [$house, $unit]), ['status' => 'Available'])
        ->assertRedirect()
        ->assertSessionHas('success');
    expect($unit->fresh()->status)->toBe('Available');
    $this->get(route('public.house', $house))->assertSee('Visibility Suite');
});

test('public transient cards do not underline text when hovered', function () {
    $owner = createHouseManagementUser('Owner', 'public-card-owner@example.com');
    createManagedHouse($owner, 'Card Retreat');

    $this->get(route('public.transients'))
        ->assertSee('class="house-card"', false);

    $styles = file_get_contents(resource_path('css/legacy.css'));
    expect($styles)
        ->toContain('.house-card, .house-card:hover')
        ->toContain('text-decoration: none');
});

test('staff can manage only houses assigned by a redeemed staff invitation', function () {
    $owner = createHouseManagementUser('Owner', 'assigned-house-owner@example.com');
    $staff = createHouseManagementUser('Staff', 'assigned-staff@example.com');
    $assignedHouse = createManagedHouse($owner, 'Assigned House');
    $otherOwner = createHouseManagementUser('Owner', 'unassigned-house-owner@example.com');
    $unassignedHouse = createManagedHouse($otherOwner, 'Unassigned House');
    $unassignedUnit = $unassignedHouse->units()->create([
        'unit_type' => 'Private Unit',
        'capacity' => 2,
        'price' => 600,
        'status' => 'Available',
    ]);

    Invitation::create([
        'invited_by' => $owner->user_id,
        'house_id' => $assignedHouse->house_id,
        'role_id' => Role::where('name', 'Staff')->firstOrFail()->role_id,
        'email' => $staff->email,
        'token' => 'used-staff-house-invite',
        'used_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($staff)
        ->get(route('staff.houses.index'))
        ->assertSee('Assigned House')
        ->assertSee('Transient Houses')
        ->assertDontSee('Unassigned House');
    $this->get(route('staff.houses.units', $assignedHouse))->assertOk();
    $this->get(route('staff.houses.units', $unassignedHouse))->assertNotFound();
    $this->get(route('staff.houses.edit', $unassignedHouse))->assertNotFound();
    $this->get(route('staff.houses.units.edit', [$assignedHouse, $unassignedUnit]))->assertNotFound();
    $this->post(route('staff.houses.units.store', $assignedHouse), [
        'unit_type' => 'Assigned Staff Unit',
        'capacity' => 2,
        'price' => 900,
        'status' => 'Available',
    ])->assertRedirect(route('staff.houses.units', $assignedHouse));

    $this->assertDatabaseHas('units', [
        'house_id' => $assignedHouse->house_id,
        'unit_type' => 'Assigned Staff Unit',
    ]);
});
