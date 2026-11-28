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
        Schema::dropIfExists('conversation_message_reactions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};
