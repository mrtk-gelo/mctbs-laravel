<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->enum('payment_type', ['downpayment', 'full', 'partial', 'balance']);
            $table->enum('payment_method', ['cash', 'gcash', 'bank_transfer']);
            $table->decimal('amount', 10, 2);
            $table->string('reference_number')->nullable();
            $table->string('receipt_path')->nullable();
            $table->integer('num_guests')->nullable();
            $table->date('checkout_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('archived_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments_archive');
    }
};
