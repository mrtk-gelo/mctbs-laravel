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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('ticket_email_status')->default('pending');
            $table->timestamp('ticket_email_attempted_at')->nullable();
            $table->timestamp('ticket_email_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_email_status',
                'ticket_email_attempted_at',
                'ticket_email_sent_at',
            ]);
        });
    }
};
