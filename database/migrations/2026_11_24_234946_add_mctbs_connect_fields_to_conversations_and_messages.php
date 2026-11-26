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
        Schema::table('conversations', function (Blueprint $table): void {
            $table->unsignedInteger('house_id')->nullable()->after('conversation_id');
            $table->unsignedInteger('guest_user_id')->nullable()->after('house_id');
            $table->unsignedInteger('assigned_to')->nullable()->after('guest_user_id');
            $table->string('status', 30)->default('open')->after('assigned_to');
            $table->string('subject', 255)->nullable()->after('status');
            $table->timestamp('last_message_at')->nullable()->after('updated_at');
            $table->timestamp('resolved_at')->nullable()->after('last_message_at');

            $table->foreign('house_id')
                ->references('house_id')
                ->on('transient_houses')
                ->restrictOnDelete();
            $table->foreign('guest_user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
            $table->foreign('assigned_to')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['house_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        Schema::table('messages', function (Blueprint $table): void {
            $table->string('message_type', 30)->default('user')->after('message');
            $table->string('client_token', 64)->nullable()->after('message_type');
            $table->unique(['conversation_id', 'client_token']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->unsignedInteger('conversation_id')->nullable()->after('booking_id');
            $table->foreign('conversation_id')
                ->references('conversation_id')
                ->on('conversations')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropForeign(['conversation_id']);
            $table->dropColumn('conversation_id');
        });

        Schema::table('messages', function (Blueprint $table): void {
            $table->dropUnique(['conversation_id', 'client_token']);
            $table->dropColumn(['message_type', 'client_token']);
        });

        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropForeign(['house_id']);
            $table->dropForeign(['guest_user_id']);
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['house_id', 'status']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropColumn([
                'house_id',
                'guest_user_id',
                'assigned_to',
                'status',
                'subject',
                'last_message_at',
                'resolved_at',
            ]);
        });
    }
};
