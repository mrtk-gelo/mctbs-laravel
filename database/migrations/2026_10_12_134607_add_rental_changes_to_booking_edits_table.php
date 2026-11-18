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
        Schema::table('booking_edits', function (Blueprint $table) {
            $table->json('rental_changes')->nullable()->after('new_total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_edits', function (Blueprint $table) {
            $table->dropColumn('rental_changes');
        });
    }
};
