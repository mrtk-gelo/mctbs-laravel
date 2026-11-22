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
        Schema::table('transient_houses', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedInteger('archived_by')->nullable();
            $table->foreign('archived_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable();
            $table->unsignedInteger('archived_by')->nullable();
            $table->boolean('archived_with_house')->default(false);
            $table->index(['house_id', 'archived_at']);
            $table->foreign('archived_by')
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
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropIndex(['house_id', 'archived_at']);
            $table->dropColumn(['archived_at', 'archived_by', 'archived_with_house']);
        });

        Schema::table('transient_houses', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropIndex('transient_houses_archived_at_index');
            $table->dropColumn(['archived_at', 'archived_by']);
        });
    }
};
