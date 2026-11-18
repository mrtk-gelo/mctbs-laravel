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
        $systemAdminRole = Role::where('name', 'System Admin')->firstOrFail();

        User::create([
            'role_id' => $systemAdminRole->role_id,
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'sysadmin@mctbs.com',
            'phone' => null,
            'password' => Hash::make('Admin@1234'),
            'email_verified_at' => now(),
            'profile_image' => null,
            'is_active' => true,
        ]);

        $this->call(TestDataSeeder::class);
    }
}
