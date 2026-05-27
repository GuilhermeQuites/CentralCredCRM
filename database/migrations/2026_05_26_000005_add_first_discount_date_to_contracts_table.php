<?php

use App\Models\Contract;
use App\Services\FirstDiscountDateService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->date('first_discount_date')->nullable()->after('contract_date');
        });

        $calculator = new FirstDiscountDateService();

        Contract::query()
            ->where('paid_installments', 0)
            ->whereNotNull('contract_date')
            ->get()
            ->each(function (Contract $contract) use ($calculator): void {
                $contract->update([
                    'first_discount_date' => $calculator
                        ->calculate($contract->contract_date->format('Y-m-d'))
                        ->toDateString(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('first_discount_date');
        });
    }
};
