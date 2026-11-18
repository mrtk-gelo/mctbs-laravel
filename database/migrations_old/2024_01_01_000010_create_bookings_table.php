<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 30)->unique();
            $table->foreignId('unit_id')->constrained('transient_units')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('users')->cascadeOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('num_guests');
            $table->decimal('extra_guest_charge', 10, 2)->default(0);
            $table->integer('total_nights');
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('downpayment_amount', 10, 2);
            $table->decimal('remaining_balance', 10, 2);

            $table->enum('status', [
                'pending',      // guest submitted, waiting for admin review
                'accepted',     // admin approved, guest must pay downpayment
                'confirmed',    // downpayment verified, booking fully confirmed
                'checked_in',   // guest has physically arrived
                'completed',    // guest has left, booking fully done
                'rejected',     // admin rejected the booking request
                'cancelled',    // cancelled by guest or admin
                'expired',      // payment deadline passed, never paid
            ])->default('pending');

            $table->enum('payment_status', [
                'unpaid',
                'pending_verification',
                'downpaid',
                'paid',
            ])->default('unpaid');

            $table->boolean('cancellation_policy_acknowledged')->default(false);
            $table->text('guest_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('archive_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancelled_by_role', 20)->nullable();
            $table->enum('refund_status', ['none', 'requested', 'refunded'])->default('none');
            $table->text('refund_details')->nullable();
            $table->decimal('additional_downpayment_required', 10, 2)->default(0);
            $table->dateTime('payment_deadline')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['guest_id', 'status'], 'idx_bookings_guest_status');
            $table->index(['unit_id', 'check_in', 'check_out'], 'idx_bookings_unit_dates');
            $table->index('status', 'idx_bookings_status');
            $table->index('cancelled_by', 'idx_bookings_cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
