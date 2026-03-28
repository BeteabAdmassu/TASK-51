<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_read_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('message_id')->constrained('group_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('read_at');

            $table->unique(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_read_receipts');
    }
};
