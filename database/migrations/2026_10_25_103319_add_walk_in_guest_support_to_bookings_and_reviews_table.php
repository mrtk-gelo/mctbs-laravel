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
            $table->dropForeign(['user_id']);
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->string('walk_in_phone', 30)->nullable()->after('user_id');
            $table->string('walk_in_email', 191)->nullable()->after('walk_in_phone');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['walk_in_phone', 'walk_in_email']);
            $table->unsignedInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};
