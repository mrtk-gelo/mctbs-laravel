<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')
            ->where('name', 'System Admin')
            ->update([
                'name' => 'Account Admin',
                'description' => 'Manages MCTBS accounts and system-wide account operations.',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'Account Admin')
            ->update([
                'name' => 'System Admin',
                'description' => 'Manages the MCTBS system and system-wide settings.',
            ]);
    }
};
