<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedInteger('maximum_capacity')->nullable()->after('capacity');
            $table->decimal('extra_guest_fee', 10, 2)->default(0)->after('maximum_capacity');
        });

        DB::table('units')->update(['maximum_capacity' => DB::raw('capacity')]);

        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('nightly_rate', 10, 2)->nullable()->after('total_price');
            $table->decimal('room_subtotal', 10, 2)->nullable()->after('nightly_rate');
            $table->unsignedInteger('extra_guest_count')->default(0)->after('room_subtotal');
            $table->decimal('extra_guest_fee', 10, 2)->default(0)->after('extra_guest_count');
            $table->decimal('extra_guest_total', 10, 2)->default(0)->after('extra_guest_fee');
        });

        Schema::create('unit_rentals', function (Blueprint $table) {
            $table->increments('unit_rental_id');
            $table->unsignedInteger('unit_id');
            $table->string('name', 100);
            $table->text('contents')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->foreign('unit_id')->references('unit_id')->on('units')->cascadeOnDelete();
        });

        Schema::create('booking_rentals', function (Blueprint $table) {
            $table->increments('booking_rental_id');
            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('unit_rental_id')->nullable();
            $table->string('name', 100);
            $table->text('contents')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total_amount', 10, 2);
            $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            $table->foreign('unit_rental_id')->references('unit_rental_id')->on('unit_rentals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_rentals');
        Schema::dropIfExists('unit_rentals');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'nightly_rate',
                'room_subtotal',
                'extra_guest_count',
                'extra_guest_fee',
                'extra_guest_total',
            ]);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['maximum_capacity', 'extra_guest_fee']);
        });
    }
};
