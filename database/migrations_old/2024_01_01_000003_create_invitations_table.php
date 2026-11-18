<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191);
            $table->string('token')->unique();
            $table->enum('role', ['owner', 'admin', 'sysadmin']);
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->cascadeOnDelete()
                ->comment('For admin invites, which owner they belong to');
            $table->foreignId('house_id')->nullable()->constrained('transient_houses')->nullOnDelete()
                ->comment('For staff assigned to a specific house');
            $table->dateTime('used_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index('email', 'idx_invitations_email');
            $table->index('owner_id', 'idx_invitations_owner');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
