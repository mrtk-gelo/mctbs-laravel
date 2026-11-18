<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transient_houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address', 500);
            $table->string('city', 100);
            $table->string('barangay', 100)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->text('amenities')->nullable()->comment('JSON array of amenities');
            $table->text('policies')->nullable();
            $table->string('cover_photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['owner_id', 'is_active'], 'idx_houses_owner_active');
            $table->index('city', 'idx_houses_city');
            $table->index('barangay', 'idx_houses_barangay');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transient_houses');
    }
};
