<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('transient_units')->cascadeOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete()
                ->comment('The pending booking waiting');
            $table->timestamp('queue_expires_at')->comment('2 days from when they were queued');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['unit_id', 'check_in', 'check_out'], 'idx_queue_unit_dates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_queue');
    }
};
