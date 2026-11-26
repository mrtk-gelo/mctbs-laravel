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
        Schema::table('messages', function (Blueprint $table): void {
            $table->dropForeign(['sender_id']);
            $table->unsignedInteger('sender_id')->nullable()->change();
            $table->string('sender_type', 30)->default('user')->after('sender_id');
            $table->foreign('sender_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        Schema::create('chat_faqs', function (Blueprint $table): void {
            $table->increments('faq_id');
            $table->unsignedInteger('house_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->string('category', 100);
            $table->string('question', 255);
            $table->text('answer');
            $table->text('keywords')->nullable();
            $table->string('related_url', 500)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->foreign('house_id')->references('house_id')->on('transient_houses')->nullOnDelete();
            $table->foreign('created_by')->references('user_id')->on('users')->nullOnDelete();
            $table->index(['house_id', 'is_active']);
        });

        Schema::create('chat_quick_replies', function (Blueprint $table): void {
            $table->increments('quick_reply_id');
            $table->unsignedInteger('house_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->string('title', 150);
            $table->text('content');
            $table->string('category', 100)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->foreign('house_id')->references('house_id')->on('transient_houses')->nullOnDelete();
            $table->foreign('created_by')->references('user_id')->on('users')->nullOnDelete();
            $table->index(['house_id', 'is_active']);
        });

        Schema::create('conversation_notes', function (Blueprint $table): void {
            $table->increments('note_id');
            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('author_id');
            $table->text('note');
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('conversation_id')
                ->on('conversations')
                ->cascadeOnDelete();
            $table->foreign('author_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        Schema::create('assistance_settings', function (Blueprint $table): void {
            $table->increments('setting_id');
            $table->unsignedInteger('house_id');
            $table->unsignedInteger('updated_by')->nullable();
            $table->text('welcome_message')->nullable();
            $table->text('handoff_message')->nullable();
            $table->timestamps();

            $table->foreign('house_id')
                ->references('house_id')
                ->on('transient_houses')
                ->cascadeOnDelete();
            $table->foreign('updated_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
            $table->unique('house_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistance_settings');
        Schema::dropIfExists('conversation_notes');
        Schema::dropIfExists('chat_quick_replies');
        Schema::dropIfExists('chat_faqs');

        Schema::table('messages', function (Blueprint $table): void {
            $table->dropForeign(['sender_id']);
            $table->dropColumn('sender_type');
            $table->unsignedInteger('sender_id')->nullable(false)->change();
            $table->foreign('sender_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};
