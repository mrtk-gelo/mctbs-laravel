<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! collect(Schema::getIndexes('bookings'))->contains(
            fn (array $index): bool => $index['unique'] && $index['columns'] === ['feedback_token']
        )) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->unique('feedback_token');
            });
        }
    }

    public function down(): void
    {
        $index = collect(Schema::getIndexes('bookings'))
            ->first(fn (array $index): bool => $index['unique'] && $index['columns'] === ['feedback_token']);

        if ($index !== null) {
            Schema::table('bookings', function (Blueprint $table) use ($index): void {
                $table->dropUnique($index['name']);
            });
        }
    }
};
