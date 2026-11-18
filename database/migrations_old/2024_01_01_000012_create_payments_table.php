<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->enum('payment_type', ['downpayment', 'full', 'partial', 'balance']);
            $table->enum('payment_method', ['cash', 'gcash', 'bank_transfer']);
            $table->decimal('amount', 10, 2);
            $table->string('reference_number')->nullable();
            $table->string('receipt_path')->nullable()
                ->comment('Stores the Google Drive file reference, not a local path');
            $table->integer('num_guests')->nullable();
            $table->date('checkout_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();

            $table->index(['booking_id', 'status'], 'idx_payments_booking_status');
            $table->index('status', 'idx_payments_status');
            $table->index('payment_method', 'idx_payments_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
