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
            $table->unsignedTinyInteger('child_free_through_age')->nullable()->after('child_count');
        });

        Schema::table('booking_guests', function (Blueprint $table) {
            $table->string('attendance_status', 20)->nullable()->after('is_primary_guest');
            $table->unsignedInteger('replaces_booking_guest_id')->nullable()->after('attendance_status');
            $table->string('identity_verification_status', 20)->nullable()->after('replaces_booking_guest_id');
            $table->unsignedInteger('identity_verified_by')->nullable()->after('identity_verification_status');
            $table->dateTime('identity_verified_at')->nullable()->after('identity_verified_by');
            $table->text('identity_exception_reason')->nullable()->after('identity_verified_at');
            $table->boolean('classification_overridden')->nullable()->after('identity_exception_reason');
            $table->text('classification_override_reason')->nullable()->after('classification_overridden');
            $table->unsignedInteger('classification_overridden_by')->nullable()->after('classification_override_reason');
            $table->dateTime('classification_overridden_at')->nullable()->after('classification_overridden_by');

            $table->foreign('replaces_booking_guest_id')
                ->references('booking_guest_id')
                ->on('booking_guests')
                ->restrictOnDelete();
            $table->foreign('identity_verified_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('classification_overridden_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_guests', function (Blueprint $table) {
            $table->dropForeign(['replaces_booking_guest_id']);
            $table->dropForeign(['identity_verified_by']);
            $table->dropForeign(['classification_overridden_by']);
            $table->dropColumn([
                'attendance_status',
                'replaces_booking_guest_id',
                'identity_verification_status',
                'identity_verified_by',
                'identity_verified_at',
                'identity_exception_reason',
                'classification_overridden',
                'classification_override_reason',
                'classification_overridden_by',
                'classification_overridden_at',
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('child_free_through_age');
        });
    }
};
