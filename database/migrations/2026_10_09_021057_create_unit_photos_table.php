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
        Schema::create('unit_photos', function (Blueprint $table) {
            $table->increments('photo_id');
            $table->unsignedInteger('unit_id');
            $table->string('photo_path', 255);
            $table->boolean('is_cover')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->foreign('unit_id')
                ->references('unit_id')
                ->on('units')
                ->cascadeOnDelete();

            $table->index(['unit_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_photos');
    }
};
