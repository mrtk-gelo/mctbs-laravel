<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('edited_by')->constrained('users')->cascadeOnDelete();
            $table->date('old_check_in');
            $table->date('old_check_out');
            $table->integer('old_num_guests');
            $table->decimal('old_total', 10, 2);
            $table->date('new_check_in');
            $table->date('new_check_out');
            $table->integer('new_num_guests');
            $table->decimal('new_total', 10, 2);
            $table->text('reason')->nullable();
            $table->decimal('additional_downpayment', 10, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_edits');
    }
};
