<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transient_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained('transient_houses')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('max_guests')->default(1);
            $table->decimal('price_per_night', 10, 2);
            $table->text('amenities')->nullable()->comment('JSON array');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['house_id', 'is_active'], 'idx_units_house_active');
            $table->index('price_per_night', 'idx_units_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transient_units');
    }
};
