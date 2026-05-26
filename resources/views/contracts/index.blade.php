@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">Contratos</h1>
            <p class="mt-1 text-sm text-slate-600">Contratos cadastrados na carteira.</p>
        </div>
        <a href="{{ route('contracts.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Novo contrato</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('contracts.index') }}" class="border-b border-slate-200 p-4">
            <div class="relative max-w-md">
                <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input name="search" type="search" value="{{ $search }}" placeholder="Buscar por nome ou CPF do cliente" class="block w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">Banco</th>
                        <th class="px-5 py-3">Convenio</th>
                        <th class="px-5 py-3">Tipo</th>
                        <th class="px-5 py-3">Valor</th>
                        <th class="px-5 py-3">Parcelas</th>
                        <th class="px-5 py-3">Contrato</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($contracts as $contract)
                        @php($refinancing = $contract->refinancingStatus())
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $contract->client->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->bankName() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->agreementName() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->contractTypeLabel() }}</td>
                            <td class="px-5 py-3 text-slate-600">R$ {{ number_format((float) $contract->contract_value, 2, ',', '.') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->paid_installments }}/{{ $contract->total_installments }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->contract_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3">@include('contracts.partials.refinancing-badge', compact('refinancing'))</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('contracts.show', $contract) }}" class="font-medium text-emerald-700 hover:text-emerald-900">Visualizar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-slate-500">Nenhum contrato encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-5 py-4">{{ $contracts->links() }}</div>
    </div>
@endsection
