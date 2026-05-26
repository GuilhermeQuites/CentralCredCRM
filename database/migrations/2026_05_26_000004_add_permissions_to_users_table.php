<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role');
        });

        $defaultPermissions = [
            'editar_cliente',
            'excluir_cliente',
            'editar_contrato',
            'excluir_contrato',
            'visualizar_bancos',
            'criar_bancos',
            'editar_bancos',
            'excluir_bancos',
            'visualizar_convenios',
            'criar_convenio',
            'editar_convenio',
            'excluir_convenio',
        ];

        User::query()->get()->each(function (User $user) use ($defaultPermissions): void {
            $user->update([
                'permissions' => $user->isAdmin()
                    ? array_keys(User::PERMISSIONS)
                    : $defaultPermissions,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
