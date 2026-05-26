@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">Fila de refinanciamento</h1>
            <p class="mt-1 text-sm text-slate-600">Oportunidades calculadas pela regra de cada contrato.</p>
        </div>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ([
            'eligible' => 'Elegiveis',
            'waiting' => 'Nao elegiveis',
            'up_to_3' => 'Faltam ate 3',
            'up_to_6' => 'Faltam ate 6',
        ] as $key => $label)
            <a href="{{ route('contracts.refinancing', ['filter' => $key]) }}" class="rounded-md px-3 py-2 text-sm font-semibold {{ $filter === $key ? 'bg-emerald-700 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">Telefone</th>
                        <th class="px-5 py-3">Vendedor</th>
                        <th class="px-5 py-3">Banco</th>
                        <th class="px-5 py-3">Convenio</th>
                        <th class="px-5 py-3">Tipo</th>
                        <th class="px-5 py-3">Parcelas pagas</th>
                        <th class="px-5 py-3">Minimo</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($contracts as $contract)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $contract->client->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->client->phone }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->client->user?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->bankName() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->agreementName() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->contractTypeLabel() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->paid_installments }}/{{ $contract->total_installments }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $contract->minimum_installments_for_refinancing }}</td>
                            <td class="px-5 py-3">@include('contracts.partials.refinancing-badge', ['refinancing' => $contract->refinancing])</td>
                            <td class="px-5 py-3 text-right"><a href="{{ route('contracts.show', $contract) }}" class="font-medium text-emerald-700">Abrir</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-slate-500">Nenhum contrato para este filtro.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
