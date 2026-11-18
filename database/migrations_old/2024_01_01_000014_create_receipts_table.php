<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('receipt_number', 50)->unique();
            $table->timestamp('generated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
