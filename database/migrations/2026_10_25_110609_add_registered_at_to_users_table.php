<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('registered_at')->nullable();
        });

        DB::table('users')
            ->whereIn('role_id', DB::table('roles')->select('role_id')->where('name', 'Owner'))
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('invitations')
                    ->whereColumn('invitations.email', 'users.email')
                    ->whereColumn('invitations.role_id', 'users.role_id')
                    ->whereNotNull('invitations.used_at');
            })
            ->update([
                'registered_at' => DB::raw('(SELECT MAX(used_at) FROM invitations WHERE invitations.email = users.email AND invitations.role_id = users.role_id)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('registered_at');
        });
    }
};
