<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_frequency_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('priority', ['normal', 'high']);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'priority', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_frequency_logs');
    }
};
