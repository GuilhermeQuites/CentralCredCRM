@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-600">Visao geral da carteira e oportunidades recentes.</p>
        </div>
        <a href="{{ route('contracts.refinancing') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Ver fila</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            'Clientes cadastrados' => $clientsCount,
            'Contratos ativos' => $activeContractsCount,
            'Elegiveis hoje' => $eligibleCount,
            'Clientes em acompanhamento' => $followUpCount,
        ] as $label => $value)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-950">Contratos em acompanhamento</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">CPF</th>
                        <th class="px-5 py-3">Telefone</th>
                        <th class="px-5 py-3">Banco</th>
                        <th class="px-5 py-3">Parcelas pagas</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($contracts as $contract)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">
                                <a href="{{ route('contracts.show', $contract) }}">{{ $contract->client->name }}</a>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->client->cpf }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->client->phone }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->bankName() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->paid_installments }}/{{ $contract->total_installments }}</td>
                            <td class="px-5 py-3">
                                @include('contracts.partials.refinancing-badge', ['refinancing' => $contract->refinancing])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">Nenhum contrato ativo cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
