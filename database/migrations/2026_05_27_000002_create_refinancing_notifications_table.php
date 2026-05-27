<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refinancing_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->unique()->constrained('contracts')->cascadeOnDelete();
            $table->string('status')->default('pendente');
            $table->timestamp('viewed_at')->nullable();
            $table->integer('notify_after_paid_installments')->nullable();
            $table->timestamp('marked_not_refinanced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refinancing_notifications');
    }
};
