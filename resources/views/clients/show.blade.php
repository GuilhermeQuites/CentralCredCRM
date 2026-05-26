@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">{{ $client->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $client->cpf }} | {{ $client->phone }}</p>
        </div>
        @if (auth()->user()->hasAnyPermission(['editar_cliente', 'excluir_cliente']))
            <div class="flex gap-2">
                @if (auth()->user()->hasPermission('editar_cliente'))
                    <a href="{{ route('clients.edit', $client) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Editar</a>
                @endif
                @if (auth()->user()->hasPermission('excluir_cliente'))
                    <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Excluir este cliente?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Excluir</button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
            <h2 class="font-semibold text-slate-950">Dados</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-slate-500">Vendedor</dt><dd class="font-medium">{{ $client->user?->name ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Nascimento</dt><dd class="font-medium">{{ $client->birth_date?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Observacoes</dt><dd class="whitespace-pre-line">{{ $client->notes ?: '-' }}</dd></div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-950">Contratos</h2>
                <a href="{{ route('contracts.create', ['client_id' => $client->id]) }}" class="text-sm font-semibold text-emerald-700">Novo contrato</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Banco</th>
                            <th class="px-5 py-3">Parcelas</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($client->contracts as $contract)
                            @php($refinancing = $contract->refinancingStatus())
                            <tr>
                                <td class="px-5 py-3 font-medium">{{ $contract->bankName() }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $contract->paid_installments }}/{{ $contract->total_installments }}</td>
                                <td class="px-5 py-3">@include('contracts.partials.refinancing-badge', compact('refinancing'))</td>
                                <td class="px-5 py-3 text-right"><a href="{{ route('contracts.show', $contract) }}" class="font-medium text-emerald-700">Abrir</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Nenhum contrato cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
