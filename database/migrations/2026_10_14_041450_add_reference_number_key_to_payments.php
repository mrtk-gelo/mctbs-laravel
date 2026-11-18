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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference_number_key', 64)->nullable();
        });

        $firstPaymentByKey = [];
        $duplicateKeys = [];
        $normalizeReference = static fn (?string $referenceNumber): string => mb_strtolower(trim($referenceNumber ?? ''));

        DB::table('payments')
            ->select(['payment_id', 'reference_number'])
            ->whereNotNull('reference_number')
            ->orderBy('payment_id')
            ->chunkById(500, function ($payments) use (&$firstPaymentByKey, &$duplicateKeys, $normalizeReference): void {
                foreach ($payments as $payment) {
                    $normalizedReference = $normalizeReference($payment->reference_number);
                    if ($normalizedReference === '') {
                        continue;
                    }

                    $key = hash('sha256', $normalizedReference);
                    if (isset($firstPaymentByKey[$key])) {
                        $duplicateKeys[$key] = true;
                    } else {
                        $firstPaymentByKey[$key] = $payment->payment_id;
                    }
                }
            }, 'payment_id');

        DB::table('payments')
            ->select(['payment_id', 'reference_number'])
            ->whereNotNull('reference_number')
            ->orderBy('payment_id')
            ->chunkById(500, function ($payments) use ($duplicateKeys, $normalizeReference): void {
                foreach ($payments as $payment) {
                    $normalizedReference = $normalizeReference($payment->reference_number);
                    if ($normalizedReference === '') {
                        continue;
                    }

                    $key = hash('sha256', $normalizedReference);
                    if (isset($duplicateKeys[$key])) {
                        $key = hash('sha256', $normalizedReference."\0legacy:".$payment->payment_id);
                    }

                    DB::table('payments')
                        ->where('payment_id', $payment->payment_id)
                        ->update(['reference_number_key' => $key]);
                }
            }, 'payment_id');

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('reference_number_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['reference_number_key']);
            $table->dropColumn('reference_number_key');
        });
    }
};
