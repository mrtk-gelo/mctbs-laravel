<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transient_houses', function (Blueprint $table) {
            $table->text('nearby_places')->nullable()->comment('JSON array of {name, type, distance}')->after('amenities');
        });
    }

    public function down(): void
    {
        Schema::table('transient_houses', function (Blueprint $table) {
            $table->dropColumn('nearby_places');
        });
    }
};
