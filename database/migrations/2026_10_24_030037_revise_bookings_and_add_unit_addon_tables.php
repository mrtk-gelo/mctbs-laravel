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
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'booking_reference')) {
                $table->string('booking_reference', 32)->nullable();
            }
            if (! Schema::hasColumn('bookings', 'feedback_token')) {
                $table->string('feedback_token', 100)->nullable();
            }
            if (! Schema::hasColumn('bookings', 'feedback_token_expires_at')) {
                $table->timestamp('feedback_token_expires_at')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'checked_in_at')) {
                $table->dateTime('checked_in_at')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'checked_out_at')) {
                $table->dateTime('checked_out_at')->nullable();
            }
        });

        DB::table('bookings')
            ->select('booking_id')
            ->whereNull('booking_reference')
            ->orderBy('booking_id')
            ->chunkById(500, function ($bookings): void {
                foreach ($bookings as $booking) {
                    DB::table('bookings')
                        ->where('booking_id', $booking->booking_id)
                        ->update([
                            'booking_reference' => 'MCTBS-'.str_pad((string) $booking->booking_id, 6, '0', STR_PAD_LEFT),
                        ]);
                }
            }, 'booking_id');

        if (! collect(Schema::getIndexes('bookings'))->contains(
            fn (array $index): bool => $index['unique'] && $index['columns'] === ['booking_reference']
        )) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unique('booking_reference');
            });
        }

        $discountReferenceIndex = collect(Schema::getIndexes('booking_discounts'))
            ->first(fn (array $index): bool => $index['unique'] && $index['columns'] === ['id_reference_key']);

        if ($discountReferenceIndex !== null) {
            Schema::table('booking_discounts', function (Blueprint $table) use ($discountReferenceIndex): void {
                $table->dropUnique($discountReferenceIndex['name']);
            });
        }

        if (! Schema::hasTable('unit_addons')) {
            Schema::create('unit_addons', function (Blueprint $table) {
                $table->increments('unit_addon_id');
                $table->unsignedInteger('unit_id');
                $table->string('name', 100);
                $table->decimal('price', 10, 2);
                $table->foreign('unit_id')->references('unit_id')->on('units')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('unit_damage_items')) {
            Schema::create('unit_damage_items', function (Blueprint $table) {
                $table->increments('unit_damage_item_id');
                $table->unsignedInteger('unit_id');
                $table->string('name', 100);
                $table->decimal('price', 10, 2);
                $table->foreign('unit_id')->references('unit_id')->on('units')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('booking_addons')) {
            Schema::create('booking_addons', function (Blueprint $table) {
                $table->increments('booking_addon_id');
                $table->unsignedInteger('booking_id');
                $table->string('name', 100);
                $table->decimal('price', 10, 2);
                $table->unsignedInteger('quantity');
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('booking_damages')) {
            Schema::create('booking_damages', function (Blueprint $table) {
                $table->increments('booking_damage_id');
                $table->unsignedInteger('booking_id');
                $table->string('name', 100);
                $table->decimal('price', 10, 2);
                $table->unsignedInteger('quantity');
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->increments('review_id');
                $table->unsignedInteger('booking_id');
                $table->unsignedInteger('unit_id');
                $table->unsignedInteger('user_id');
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->timestamps();
                $table->unique('booking_id');
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->restrictOnDelete();
                $table->foreign('unit_id')->references('unit_id')->on('units')->restrictOnDelete();
                $table->foreign('user_id')->references('user_id')->on('users')->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('booking_edits', 'rental_changes')) {
            Schema::table('booking_edits', function (Blueprint $table) {
                $table->dropColumn('rental_changes');
            });
        }

        Schema::dropIfExists('booking_rentals');
        Schema::dropIfExists('unit_rentals');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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

        Schema::table('booking_edits', function (Blueprint $table) {
            $table->json('rental_changes')->nullable();
        });

        Schema::dropIfExists('reviews');
        Schema::dropIfExists('booking_damages');
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('unit_damage_items');
        Schema::dropIfExists('unit_addons');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['booking_reference']);
            $table->dropColumn([
                'booking_reference',
                'feedback_token',
                'feedback_token_expires_at',
                'checked_in_at',
                'checked_out_at',
            ]);
        });
    }
};
