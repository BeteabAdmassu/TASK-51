<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_chat_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_chat_id')->constrained('group_chats');
            $table->foreignId('user_id')->constrained('users');
            $table->time('dnd_start')->default('22:00:00');
            $table->time('dnd_end')->default('07:00:00');
            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->unique(['group_chat_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_chat_participants');
    }
};
