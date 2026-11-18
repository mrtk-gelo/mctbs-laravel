<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('transient_units')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['Available', 'Booked', 'Unavailable'])->default('Available');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['unit_id', 'date'], 'unique_unit_date');
            $table->index(['unit_id', 'status'], 'idx_calendar_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_calendar');
    }
};
