<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('house_id')->nullable()->constrained('transient_houses')->nullOnDelete()
                ->comment('Specific house this admin manages');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['owner_id', 'admin_id'], 'unique_owner_admin');
            $table->index('admin_id', 'idx_owner_admins_admin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_admins');
    }
};
