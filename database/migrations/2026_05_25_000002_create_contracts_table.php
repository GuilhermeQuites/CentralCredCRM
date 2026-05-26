<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('bank');
            $table->decimal('contract_value', 10, 2);
            $table->decimal('installment_value', 10, 2);
            $table->integer('total_installments');
            $table->integer('paid_installments');
            $table->integer('minimum_installments_for_refinancing');
            $table->date('contract_date');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
