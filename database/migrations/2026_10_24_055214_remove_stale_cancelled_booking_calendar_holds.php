<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('bookings')
            ->whereIn('status', ['Cancelled', 'Rejected'])
            ->select(['booking_id', 'unit_id'])
            ->orderBy('booking_id')
            ->chunkById(500, function ($bookings): void {
                foreach ($bookings as $booking) {
                    DB::table('unit_availability')
                        ->where('unit_id', $booking->unit_id)
                        ->where('status', 'Booked')
                        ->where('note', 'Confirmed booking #'.$booking->booking_id)
                        ->delete();
                }
            }, 'booking_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Stale holds removed by this migration must not be restored.
    }
};
