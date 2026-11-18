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
        Schema::create('booking_receipts', function (Blueprint $table) {
            $table->increments('receipt_id');
            $table->unsignedInteger('booking_id')->unique();
            $table->string('receipt_number', 40)->unique();
            $table->timestamp('issued_at')->useCurrent();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_receipts');
    }
};
