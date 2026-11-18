<?php

namespace Database\Seeders;

use App\Models\Invitation;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('name', 'Owner')->firstOrFail();
        $guestRole = Role::where('name', 'Guest')->firstOrFail();
        $staffRole = Role::where('name', 'Staff')->firstOrFail();

        $owner = User::updateOrCreate(['email' => 'owner@test.com'], [
            'role_id' => $ownerRole->role_id,
            'first_name' => 'Test',
            'last_name' => 'Owner',
            'phone' => '09170000001',
            'password' => 'test1234',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $house = TransientHouse::updateOrCreate([
            'owner_id' => $owner->user_id,
            'name' => 'Test House',
        ], [
            'address' => '123 Sample St',
            'city' => 'Cebu City',
            'barangay' => 'Lahug',
            'contact_number' => '0321234567',
            'is_active' => true,
        ]);

        $staff = User::updateOrCreate(['email' => 'staff@test.com'], [
            'role_id' => $staffRole->role_id,
            'first_name' => 'Test',
            'last_name' => 'Staff',
            'phone' => '09170000002',
            'password' => 'test1234',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        Invitation::updateOrCreate([
            'house_id' => $house->house_id,
            'role_id' => $staffRole->role_id,
            'email' => $staff->email,
        ], [
            'invited_by' => $owner->user_id,
            'token' => Str::uuid()->toString(),
            'used_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        User::updateOrCreate(['email' => 'guest@test.com'], [
            'role_id' => $guestRole->role_id,
            'first_name' => 'Test',
            'last_name' => 'Guest',
            'phone' => '09170000003',
            'password' => 'test1234',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        Unit::updateOrCreate([
            'house_id' => $house->house_id,
            'unit_type' => 'Room 1',
        ], [
            'capacity' => 2,
            'price' => 1500,
            'status' => 'Available',
        ]);

        Unit::updateOrCreate([
            'house_id' => $house->house_id,
            'unit_type' => 'Family Room',
        ], [
            'capacity' => 5,
            'price' => 2800,
            'status' => 'Available',
        ]);
    }
}
