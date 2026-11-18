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
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->unsignedInteger('unit_damage_item_id')->nullable()->after('unit_id');
            $table->unsignedInteger('quantity')->default(1)->after('amount');
            $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
            $table->string('approval_status', 30)->nullable()->after('status');
            $table->json('approval_history')->nullable()->after('approval_status');
            $table->unsignedInteger('resolved_by')->nullable()->after('resolved_at');
            $table->text('resolution_notes')->nullable()->after('resolved_by');

            $table->foreign('unit_damage_item_id')
                ->references('unit_damage_item_id')
                ->on('unit_damage_items')
                ->nullOnDelete();

            $table->foreign('resolved_by')
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
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropForeign(['unit_damage_item_id']);
            $table->dropForeign(['resolved_by']);
            $table->dropColumn([
                'unit_damage_item_id',
                'quantity',
                'unit_price',
                'approval_status',
                'approval_history',
                'resolved_by',
                'resolution_notes',
            ]);
        });
    }
};
