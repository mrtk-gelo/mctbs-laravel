<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table): void {
            $table->time('check_in_time')->default('14:00:00');
            $table->time('check_out_time')->default('12:00:00');
            $table->string('pricing_mode', 20)->default('Per Unit');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('pricing_mode', 20)->default('Per Unit');
        });

        DB::table('bookings')
            ->whereNull('nightly_rate')
            ->orderBy('booking_id')
            ->chunkById(500, function ($bookings): void {
                foreach ($bookings as $booking) {
                    $nightlyRate = DB::table('units')
                        ->where('unit_id', $booking->unit_id)
                        ->value('price');

                    DB::table('bookings')
                        ->where('booking_id', $booking->booking_id)
                        ->update(['nightly_rate' => $nightlyRate ?? 0]);
                }
            }, 'booking_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn('pricing_mode');
        });

        Schema::table('units', function (Blueprint $table): void {
            $table->dropColumn(['check_in_time', 'check_out_time', 'pricing_mode']);
        });
    }
};
