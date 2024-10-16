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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 127);
            $table->timestamps();
        });

        
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('chat_id')->constrained();
            $table->timestamps();
            $table->text('body')->nullable();
            $table->text('deleted_body')->nullable();
            $table->index(['chat_id', 'created_at']);
        });

        Schema::create('chat_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('chat_id')->constrained();
            $table->unsignedBigInteger('last_seen_message_id')->nullable();

            $table->primary(['user_id', 'chat_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_user');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('chats'); 
    }
};
