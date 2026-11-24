<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accountAdminRole = Role::where('name', 'Account Admin')->firstOrFail();

        User::create([
            'role_id' => $accountAdminRole->role_id,
            'first_name' => 'Account',
            'last_name' => 'Admin',
            'email' => config('services.mctbs.sysad_email') ?: 'accadmin@mctbs.com',
            'phone' => null,
            'password' => Hash::make('Admin@1234'),
            'email_verified_at' => now(),
            'profile_image' => null,
            'is_active' => true,
        ]);

        $this->call(TestDataSeeder::class);
    }
}
