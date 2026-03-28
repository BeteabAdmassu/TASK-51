<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_chats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ride_order_id')->unique()->constrained('ride_orders');
            $table->enum('status', ['active', 'disbanded'])->default('active');
            $table->timestamps();
            $table->timestamp('disbanded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_chats');
    }
};
