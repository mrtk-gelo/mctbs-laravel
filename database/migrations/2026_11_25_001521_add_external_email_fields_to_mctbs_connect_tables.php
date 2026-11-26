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
            $table->string('external_channel', 30)->nullable()->after('subject');
            $table->string('external_token', 64)->nullable()->unique()->after('external_channel');
        });

        Schema::table('messages', function (Blueprint $table): void {
            $table->string('external_message_id', 255)->nullable()->unique()->after('client_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table): void {
            $table->dropUnique(['external_message_id']);
            $table->dropColumn('external_message_id');
        });

        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropUnique(['external_token']);
            $table->dropColumn(['external_channel', 'external_token']);
        });
    }
};
