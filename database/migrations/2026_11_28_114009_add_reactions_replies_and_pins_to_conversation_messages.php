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
            $table->unsignedInteger('reply_to_message_id')->nullable()->after('message_id');
            $table->foreign('reply_to_message_id')
                ->references('message_id')
                ->on('messages')
                ->nullOnDelete();
        });

        Schema::create('conversation_message_reactions', function (Blueprint $table): void {
            $table->increments('reaction_id');
            $table->unsignedInteger('message_id');
            $table->string('actor_key', 80);
            $table->string('emoji', 16);

            $table->foreign('message_id')
                ->references('message_id')
                ->on('messages')
                ->cascadeOnDelete();
            $table->unique(['message_id', 'actor_key'], 'cmp_reactions_actor_message_unique');
        });

        Schema::create('conversation_message_pins', function (Blueprint $table): void {
            $table->increments('pin_id');
            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('message_id');
            $table->string('actor_key', 80);
            $table->timestamp('pinned_at')->useCurrent();

            $table->foreign('conversation_id')
                ->references('conversation_id')
                ->on('conversations')
                ->cascadeOnDelete();
            $table->foreign('message_id')
                ->references('message_id')
                ->on('messages')
                ->cascadeOnDelete();
            $table->unique(
                ['conversation_id', 'actor_key', 'message_id'],
                'cmp_pins_actor_message_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_message_pins');
        Schema::dropIfExists('conversation_message_reactions');

        Schema::table('messages', function (Blueprint $table): void {
            $table->dropForeign(['reply_to_message_id']);
            $table->dropColumn('reply_to_message_id');
        });
    }
};
