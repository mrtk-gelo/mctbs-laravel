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
            $table->string('visitor_name', 100)->nullable()->after('guest_user_id');
            $table->string('visitor_email')->nullable()->after('visitor_name');
            $table->string('visitor_session_hash', 64)->nullable()->after('visitor_email');
            $table->index(['guest_user_id', 'visitor_email']);
            $table->index('visitor_session_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropIndex(['guest_user_id', 'visitor_email']);
            $table->dropIndex(['visitor_session_hash']);
            $table->dropColumn(['visitor_name', 'visitor_email', 'visitor_session_hash']);
        });
    }
};
