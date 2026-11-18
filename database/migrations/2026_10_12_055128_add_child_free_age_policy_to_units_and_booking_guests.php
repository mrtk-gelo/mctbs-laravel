<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedTinyInteger('child_free_through_age')->nullable()->after('capacity');
        });

        Schema::table('booking_guests', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('guest_type');
            $table->boolean('is_free_child')->default(false)->after('age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_guests', function (Blueprint $table) {
            $table->dropColumn(['age', 'is_free_child']);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('child_free_through_age');
        });
    }
};
