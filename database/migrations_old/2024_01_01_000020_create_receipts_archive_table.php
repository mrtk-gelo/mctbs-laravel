<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('receipt_number', 50)->unique();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('archived_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts_archive');
    }
};
