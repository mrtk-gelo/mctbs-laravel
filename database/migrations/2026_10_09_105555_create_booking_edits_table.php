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
        Schema::create('booking_edits', function (Blueprint $table) {
            $table->increments('edit_id');
            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('edited_by');
            $table->dateTime('old_check_in');
            $table->dateTime('old_check_out');
            $table->dateTime('new_check_in');
            $table->dateTime('new_check_out');
            $table->decimal('old_total_price', 10, 2);
            $table->decimal('new_total_price', 10, 2);
            $table->text('edit_reason');
            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();

            $table->foreign('edited_by')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();

            $table->index(['booking_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_edits');
    }
};
