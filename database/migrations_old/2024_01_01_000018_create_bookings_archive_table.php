<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Archive tables intentionally have NO foreign key constraints,
     * matching the original schema — they must keep historical rows
     * even after the referenced unit/guest/user is gone.
     */
    public function up(): void
    {
        Schema::create('bookings_archive', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 30)->unique();
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('guest_id');
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
                'pending', 'accepted', 'confirmed', 'checked_in',
                'completed', 'rejected', 'cancelled', 'expired',
            ])->default('pending');

            $table->enum('payment_status', [
                'unpaid', 'pending_verification', 'downpaid', 'paid',
            ])->default('unpaid');

            $table->boolean('cancellation_policy_acknowledged')->default(false);
            $table->text('guest_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('archive_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->string('cancelled_by_role', 20)->nullable();
            $table->enum('refund_status', ['none', 'requested', 'refunded'])->default('none');
            $table->text('refund_details')->nullable();
            $table->dateTime('payment_deadline')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('archived_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings_archive');
    }
};
