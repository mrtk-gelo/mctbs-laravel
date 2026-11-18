<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('rating')->default(5);
            $table->text('comment');
            $table->timestamp('created_at')->useCurrent();

            $table->unique('booking_id', 'uq_booking_review');
            $table->index('guest_id', 'idx_reviews_guest');
            $table->index('rating', 'idx_reviews_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_reviews');
    }
};
