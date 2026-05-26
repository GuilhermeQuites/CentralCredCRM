<?php

use App\Models\Bank;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable()->after('client_id')->constrained('banks')->nullOnDelete();
            $table->foreignId('agreement_id')->nullable()->after('bank_id')->constrained('agreements')->nullOnDelete();
            $table->string('contract_type')->default('new')->after('bank');
        });

        DB::table('contracts')
            ->whereNotNull('bank')
            ->orderBy('bank')
            ->pluck('bank')
            ->filter()
            ->unique()
            ->each(function (string $bankName): void {
                Bank::firstOrCreate(['name' => $bankName]);
            });

        DB::table('contracts')
            ->whereNotNull('bank')
            ->get(['id', 'bank'])
            ->each(function (object $contract): void {
                $bankId = Bank::where('name', $contract->bank)->value('id');
                DB::table('contracts')
                    ->where('id', $contract->id)
                    ->update(['bank_id' => $bankId]);
            });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_id');
            $table->dropConstrainedForeignId('agreement_id');
            $table->dropColumn('contract_type');
        });
    }
};
