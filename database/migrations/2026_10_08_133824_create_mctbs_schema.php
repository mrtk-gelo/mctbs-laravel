<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('role_id');
            $table->string('name', 50)->unique();
            $table->string('description', 255)->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->unsignedInteger('role_id');

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('password', 255);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('profile_image', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);

            $table->foreign('role_id')
                ->references('role_id')
                ->on('roles')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | TRANSIENT HOUSES
        |--------------------------------------------------------------------------
        */
        Schema::create('transient_houses', function (Blueprint $table) {
            $table->increments('house_id');
            $table->unsignedInteger('owner_id');

            $table->string('name', 255);
            $table->string('address', 500);
            $table->text('description')->nullable();
            $table->string('city', 100);
            $table->string('barangay', 100)->nullable();
            $table->string('contact_number', 30);
            $table->text('policies')->nullable();
            $table->string('cover_photo', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);

            $table->foreign('owner_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | UNITS
        |--------------------------------------------------------------------------
        */
        Schema::create('units', function (Blueprint $table) {
            $table->increments('unit_id');
            $table->unsignedInteger('house_id');

            $table->string('unit_type', 100);
            $table->integer('capacity');
            $table->decimal('price', 10, 2);
            $table->string('status', 30);
            $table->text('description')->nullable();

            $table->foreign('house_id')
                ->references('house_id')
                ->on('transient_houses')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | AMENITIES
        |--------------------------------------------------------------------------
        */
        Schema::create('amenities', function (Blueprint $table) {
            $table->increments('amenity_id');
            $table->string('name', 100)->unique();
            $table->string('description', 255)->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | HOUSE AMENITIES
        |--------------------------------------------------------------------------
        */
        Schema::create('house_amenities', function (Blueprint $table) {
            $table->increments('house_amenity_id');
            $table->unsignedInteger('house_id');
            $table->unsignedInteger('amenity_id');

            $table->foreign('house_id')
                ->references('house_id')
                ->on('transient_houses')
                ->cascadeOnDelete();

            $table->foreign('amenity_id')
                ->references('amenity_id')
                ->on('amenities')
                ->cascadeOnDelete();

            // Prevent the same amenity from being added twice to one house.
            $table->unique(['house_id', 'amenity_id']);
        });

        /*
        |--------------------------------------------------------------------------
        | UNIT AVAILABILITY
        |--------------------------------------------------------------------------
        */
        Schema::create('unit_availability', function (Blueprint $table) {
            $table->increments('availability_id');
            $table->unsignedInteger('unit_id');

            $table->date('date');
            $table->string('status', 30);
            $table->string('note', 255)->nullable();

            $table->foreign('unit_id')
                ->references('unit_id')
                ->on('units')
                ->cascadeOnDelete();

            // One availability record per unit per date.
            $table->unique(['unit_id', 'date']);
        });

        /*
        |--------------------------------------------------------------------------
        | BOOKINGS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | discount_id was removed from this table.
        | Discounts are stored separately in booking_discounts.
        |
        |--------------------------------------------------------------------------
        */
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('booking_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('unit_id');

            $table->dateTime('check_in');
            $table->dateTime('check_out');
            $table->integer('adult_count');
            $table->integer('child_count');
            $table->decimal('total_price', 10, 2);
            $table->string('status', 30);
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('unit_id')
                ->references('unit_id')
                ->on('units')
                ->restrictOnDelete();

            // Helps search bookings by unit and dates.
            $table->index(['unit_id', 'check_in', 'check_out']);
        });

        /*
        |--------------------------------------------------------------------------
        | BOOKING GUESTS
        |--------------------------------------------------------------------------
        */
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->increments('booking_guest_id');
            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('user_id')->nullable();

            $table->string('full_name', 200);
            $table->string('guest_type', 30);
            $table->decimal('share_amount', 10, 2);
            $table->tinyInteger('is_primary_guest')->default(0);

            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | BOOKING DISCOUNTS
        |--------------------------------------------------------------------------
        |
        | Each discount request belongs to a booking and a specific guest.
        |
        |--------------------------------------------------------------------------
        */
        Schema::create('booking_discounts', function (Blueprint $table) {
            $table->increments('discount_id');

            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('booking_guest_id');
            $table->unsignedInteger('verified_by')->nullable();

            $table->string('discount_type', 30);
            $table->string('id_reference', 100);
            $table->string('verification_status', 30);

            $table->dateTime('verified_at')->nullable();

            $table->decimal('eligible_amount', 10, 2);
            $table->decimal('discount_rate', 5, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('final_amount', 10, 2);

            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('refund_status', 30);

            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->cascadeOnDelete();

            $table->foreign('booking_guest_id')
                ->references('booking_guest_id')
                ->on('booking_guests')
                ->cascadeOnDelete();

            $table->foreign('verified_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | PAYMENTS
        |--------------------------------------------------------------------------
        */
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedInteger('booking_id');

            $table->string('payment_method', 50);
            $table->decimal('amount', 10, 2);
            $table->string('payment_status', 30);
            $table->dateTime('payment_date');

            $table->string('reference_number', 100)->nullable();

            $table->string('paymongo_payment_id', 255)->nullable();
            $table->string('paymongo_payment_intent_id', 255)->nullable();
            $table->string('paymongo_checkout_session_id', 255)->nullable();

            $table->string('payment_receipt', 255)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | REFUNDS
        |--------------------------------------------------------------------------
        */
        Schema::create('refunds', function (Blueprint $table) {
            $table->increments('refund_id');

            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('payment_id');
            $table->unsignedInteger('processed_by')->nullable();

            $table->decimal('amount', 10, 2);
            $table->string('reason', 255);
            $table->string('refund_status', 30);
            $table->string('refund_reference', 100)->nullable();
            $table->dateTime('processed_at')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();

            $table->foreign('payment_id')
                ->references('payment_id')
                ->on('payments')
                ->restrictOnDelete();

            $table->foreign('processed_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | ADDITIONAL CHARGES
        |--------------------------------------------------------------------------
        */
        Schema::create('additional_charges', function (Blueprint $table) {
            $table->increments('additional_charge_id');

            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('created_by');

            $table->string('description', 255);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);

            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();

            $table->foreign('created_by')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | DAMAGE REPORTS
        |--------------------------------------------------------------------------
        */
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->increments('damage_id');

            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('unit_id');
            $table->unsignedInteger('reported_by');

            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->string('status', 30);
            $table->dateTime('reported_at');
            $table->dateTime('resolved_at')->nullable();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();

            $table->foreign('unit_id')
                ->references('unit_id')
                ->on('units')
                ->restrictOnDelete();

            $table->foreign('reported_by')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | FEEDBACK
        |--------------------------------------------------------------------------
        */
        Schema::create('feedback', function (Blueprint $table) {
            $table->increments('feedback_id');

            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('unit_id');

            $table->integer('rating');
            $table->text('review')->nullable();
            $table->string('status', 30);

            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('unit_id')
                ->references('unit_id')
                ->on('units')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | CONVERSATIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('conversations', function (Blueprint $table) {
            $table->increments('conversation_id');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        /*
        |--------------------------------------------------------------------------
        | CONVERSATION PARTICIPANTS
        |--------------------------------------------------------------------------
        */
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->increments('participant_id');

            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('user_id');

            $table->timestamp('joined_at')->useCurrent();

            $table->foreign('conversation_id')
                ->references('conversation_id')
                ->on('conversations')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            // A user cannot be added twice to the same conversation.
            $table->unique(['conversation_id', 'user_id']);
        });

        /*
        |--------------------------------------------------------------------------
        | MESSAGES
        |--------------------------------------------------------------------------
        */
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('message_id');

            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('sender_id');

            $table->text('message');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('read_at')->nullable();

            $table->foreign('conversation_id')
                ->references('conversation_id')
                ->on('conversations')
                ->cascadeOnDelete();

            $table->foreign('sender_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('notification_id');

            $table->unsignedInteger('user_id');
            $table->unsignedInteger('booking_id')->nullable();

            $table->string('type', 50);
            $table->string('title', 255);
            $table->text('message');

            $table->tinyInteger('is_read')->default(0);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('read_at')->nullable();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | INVITATIONS
        |--------------------------------------------------------------------------
        |
        | role_id references the same roles table used by users.
        |
        | Available roles:
        | 1 = Account Admin
        | 2 = Owner
        | 3 = Staff
        | 4 = Guest
        |
        |--------------------------------------------------------------------------
        */
        Schema::create('invitations', function (Blueprint $table) {
            $table->increments('invitation_id');

            $table->unsignedInteger('invited_by');
            $table->unsignedInteger('house_id')->nullable();
            $table->unsignedInteger('role_id');

            $table->string('email', 191);
            $table->string('token', 255)->unique();

            $table->dateTime('used_at')->nullable();
            $table->timestamp('expires_at');

            $table->foreign('invited_by')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('house_id')
                ->references('house_id')
                ->on('transient_houses')
                ->nullOnDelete();

            $table->foreign('role_id')
                ->references('role_id')
                ->on('roles')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | TRANSACTION ARCHIVES
        |--------------------------------------------------------------------------
        */
        Schema::create('transaction_archives', function (Blueprint $table) {
            $table->increments('archive_id');

            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('archived_by');

            $table->text('archive_note')->nullable();
            $table->timestamp('archived_at')->useCurrent();
            $table->timestamp('scheduled_deletion_at')->nullable();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->restrictOnDelete();

            $table->foreign('archived_by')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | REPORTS
        |--------------------------------------------------------------------------
        */
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('report_id');

            $table->unsignedInteger('house_id')->nullable();
            $table->unsignedInteger('generated_by');

            $table->string('report_type', 100);
            $table->date('date_from');
            $table->date('date_to');
            $table->string('file_path', 255)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('house_id')
                ->references('house_id')
                ->on('transient_houses')
                ->nullOnDelete();

            $table->foreign('generated_by')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | EMAIL VERIFICATIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->increments('verification_id');
            $table->unsignedInteger('user_id');

            $table->string('token', 255)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | PASSWORD RESETS
        |--------------------------------------------------------------------------
        */
        Schema::create('password_resets', function (Blueprint $table) {
            $table->increments('reset_id');
            $table->unsignedInteger('user_id');

            $table->string('token', 255)->unique();
            $table->timestamp('expires_at');

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | DEFAULT ROLES
        |--------------------------------------------------------------------------
        */
        DB::table('roles')->insert([
            [
                'name' => 'Account Admin',
                'description' => 'Manages MCTBS accounts and system-wide account operations.',
            ],
            [
                'name' => 'Owner',
                'description' => 'Manages transient houses, units, bookings, and related operations.',
            ],
            [
                'name' => 'Staff',
                'description' => 'Assists with booking, guest, payment, and house operations.',
            ],
            [
                'name' => 'Guest',
                'description' => 'Uses the system to search, book, and manage guest-related transactions.',
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('email_verifications');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('transaction_archives');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('damage_reports');
        Schema::dropIfExists('additional_charges');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_discounts');
        Schema::dropIfExists('booking_guests');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('unit_availability');
        Schema::dropIfExists('house_amenities');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('units');
        Schema::dropIfExists('transient_houses');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
