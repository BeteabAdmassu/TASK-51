<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_frequency_logs', function (Blueprint $table): void {
            $table->string('type', 50)->nullable()->after('priority');
            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notification_frequency_logs', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'type', 'created_at']);
            $table->dropColumn('type');
        });
    }
};
