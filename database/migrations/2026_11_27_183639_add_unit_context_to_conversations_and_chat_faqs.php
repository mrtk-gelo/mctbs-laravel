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
            $table->unsignedInteger('unit_id')->nullable()->after('house_id');
            $table->foreign('unit_id')->references('unit_id')->on('units')->nullOnDelete();
            $table->index(['unit_id', 'last_message_at']);
        });

        Schema::table('chat_faqs', function (Blueprint $table): void {
            $table->unsignedInteger('unit_id')->nullable()->after('house_id');
            $table->foreign('unit_id')->references('unit_id')->on('units')->nullOnDelete();
            $table->index(['unit_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_faqs', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id', 'is_active']);
            $table->dropColumn('unit_id');
        });

        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id', 'last_message_at']);
            $table->dropColumn('unit_id');
        });
    }
};
