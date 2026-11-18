<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->unique();
            $table->string('password');
            $table->enum('role', ['sysadmin', 'owner', 'admin', 'guest'])->default('guest');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 30)->nullable();
            $table->string('profile_photo')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deactivated')->default(false);
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role', 'idx_users_role');
            $table->index('deleted_at', 'idx_users_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
