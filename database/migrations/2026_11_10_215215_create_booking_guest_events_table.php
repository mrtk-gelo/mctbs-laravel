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
        Schema::create('booking_guest_events', function (Blueprint $table) {
            $table->increments('booking_guest_event_id');
            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('booking_guest_id')->nullable();
            $table->unsignedInteger('related_booking_guest_id')->nullable();
            $table->string('event_type', 40);
            $table->unsignedInteger('performed_by')->nullable();
            $table->text('reason')->nullable();
            $table->dateTime('event_at');
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();
            $table->foreign('booking_guest_id')
                ->references('booking_guest_id')
                ->on('booking_guests')
                ->nullOnDelete();
            $table->foreign('related_booking_guest_id')
                ->references('booking_guest_id')
                ->on('booking_guests')
                ->nullOnDelete();
            $table->foreign('performed_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['booking_id', 'event_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_guest_events');
    }
};
