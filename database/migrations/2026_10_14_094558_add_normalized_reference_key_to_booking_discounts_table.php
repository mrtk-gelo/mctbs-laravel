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
        Schema::table('booking_discounts', function (Blueprint $table) {
            $table->string('id_reference_key', 64)->nullable();
        });

        $firstDiscountByKey = [];
        $duplicateKeys = [];
        $normalizeReference = static fn (?string $reference): string => mb_strtolower(trim($reference ?? ''));

        DB::table('booking_discounts')
            ->select(['discount_id', 'id_reference'])
            ->orderBy('discount_id')
            ->chunkById(500, function ($discounts) use (&$firstDiscountByKey, &$duplicateKeys, $normalizeReference): void {
                foreach ($discounts as $discount) {
                    $normalizedReference = $normalizeReference($discount->id_reference);
                    if ($normalizedReference === '') {
                        continue;
                    }

                    $key = hash('sha256', $normalizedReference);
                    if (isset($firstDiscountByKey[$key])) {
                        $duplicateKeys[$key] = true;
                    } else {
                        $firstDiscountByKey[$key] = $discount->discount_id;
                    }
                }
            }, 'discount_id');

        DB::table('booking_discounts')
            ->select(['discount_id', 'id_reference'])
            ->orderBy('discount_id')
            ->chunkById(500, function ($discounts) use ($duplicateKeys, $normalizeReference): void {
                foreach ($discounts as $discount) {
                    $normalizedReference = $normalizeReference($discount->id_reference);
                    if ($normalizedReference === '') {
                        continue;
                    }

                    $key = hash('sha256', $normalizedReference);
                    if (isset($duplicateKeys[$key])) {
                        $key = hash('sha256', $normalizedReference."\0legacy:".$discount->discount_id);
                    }

                    DB::table('booking_discounts')
                        ->where('discount_id', $discount->discount_id)
                        ->update(['id_reference_key' => $key]);
                }
            }, 'discount_id');

        Schema::table('booking_discounts', function (Blueprint $table) {
            $table->unique('id_reference_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_discounts', function (Blueprint $table) {
            $table->dropUnique(['id_reference_key']);
            $table->dropColumn('id_reference_key');
        });
    }
};
