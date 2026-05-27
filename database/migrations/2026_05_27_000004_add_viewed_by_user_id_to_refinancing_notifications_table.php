<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refinancing_notifications', function (Blueprint $table) {
            $table->foreignId('viewed_by_user_id')
                ->nullable()
                ->after('viewed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('refinancing_notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('viewed_by_user_id');
        });
    }
};
