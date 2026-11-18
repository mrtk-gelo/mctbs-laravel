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
        Schema::table('booking_guests', function (Blueprint $table) {
            $table->string('roster_status', 20)->default('Active')->after('is_primary_guest');
            $table->dateTime('removed_at')->nullable()->after('roster_status');
            $table->unsignedInteger('removed_by')->nullable()->after('removed_at');
            $table->text('removal_reason')->nullable()->after('removed_by');

            $table->foreign('removed_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['booking_id', 'roster_status']);
        });

        DB::table('booking_guests')->update(['roster_status' => 'Active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_guests', function (Blueprint $table) {
            $table->dropForeign(['removed_by']);
            $table->dropIndex(['booking_id', 'roster_status']);
            $table->dropColumn(['roster_status', 'removed_at', 'removed_by', 'removal_reason']);
        });
    }
};
