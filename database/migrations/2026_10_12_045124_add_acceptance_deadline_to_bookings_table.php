<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('accepted_at')->nullable()->after('status');
            $table->dateTime('payment_deadline')->nullable()->after('accepted_at');
        });

        DB::table('bookings')
            ->where('status', 'Accepted')
            ->whereNotNull('updated_at')
            ->orderBy('booking_id')
            ->chunkById(100, function ($bookings): void {
                foreach ($bookings as $booking) {
                    $acceptedAt = Carbon::parse($booking->updated_at);
                    DB::table('bookings')
                        ->where('booking_id', $booking->booking_id)
                        ->update([
                            'accepted_at' => $acceptedAt,
                            'payment_deadline' => $acceptedAt->copy()->addDay(),
                        ]);
                }
            }, 'booking_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['accepted_at', 'payment_deadline']);
        });
    }
};
