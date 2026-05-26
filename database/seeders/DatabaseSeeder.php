<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ContactHistory;
use App\Models\Contract;
use App\Models\Agreement;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@centralcred.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'permissions' => array_keys(User::PERMISSIONS),
        ]);

        $sellerPermissions = [
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

        $seller = User::create([
            'name' => 'Vendedor Central',
            'email' => 'vendedor@centralcred.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'permissions' => $sellerPermissions,
        ]);

        $banks = collect(['Caixa', 'Banco do Brasil', 'Itau', 'Bradesco', 'Santander'])
            ->map(fn (string $name) => Bank::create(['name' => $name]));

        $agreements = collect(['INSS', 'SIAPE', 'Prefeitura', 'Forcas Armadas'])
            ->map(fn (string $name) => Agreement::create(['name' => $name]));

        $types = Contract::TYPES;

        for ($i = 1; $i <= 10; $i++) {
            $client = Client::create([
                'user_id' => $i % 2 === 0 ? $seller->id : $admin->id,
                'name' => "Cliente {$i}",
                'cpf' => sprintf('000.000.000-%02d', $i),
                'phone' => sprintf('(11) 90000-%04d', $i),
                'birth_date' => now()->subYears(35 + $i)->toDateString(),
                'notes' => $i % 3 === 0 ? 'Cliente com interesse em acompanhamento ativo.' : null,
            ]);

            $minimumInstallments = $i % 2 === 0 ? 28 : 24;
            $paidInstallments = match ($i) {
                1, 2, 3, 4 => $minimumInstallments + $i,
                5, 6 => $minimumInstallments - 2,
                7, 8 => $minimumInstallments - 5,
                default => $minimumInstallments - 10,
            };

            $contract = Contract::create([
                'client_id' => $client->id,
                'bank_id' => $banks[$i % $banks->count()]->id,
                'agreement_id' => $agreements[$i % $agreements->count()]->id,
                'contract_type' => $types[$i % count($types)],
                'bank' => $banks[$i % $banks->count()]->name,
                'contract_value' => 12000 + ($i * 1750),
                'installment_value' => 320 + ($i * 22),
                'total_installments' => 84,
                'paid_installments' => $paidInstallments,
                'minimum_installments_for_refinancing' => $minimumInstallments,
                'contract_date' => now()->subMonths($paidInstallments)->toDateString(),
                'status' => $i === 10 ? 'finished' : 'active',
            ]);

            if ($i <= 4) {
                ContactHistory::create([
                    'contract_id' => $contract->id,
                    'type' => $i % 2 === 0 ? 'whatsapp' : 'phone',
                    'description' => 'Contato inicial realizado para avaliar interesse.',
                    'contacted_at' => now()->subDays($i),
                ]);
            }
        }
    }
}
