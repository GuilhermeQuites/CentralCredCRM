@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">{{ $contract->client->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $contract->bankName() }} | {{ $contract->paid_installments }}/{{ $contract->total_installments }} parcelas pagas</p>
        </div>
        @if (auth()->user()->hasAnyPermission(['editar_contrato', 'excluir_contrato']))
            <div class="flex gap-2">
                @if (auth()->user()->hasPermission('editar_contrato'))
                    <a href="{{ route('contracts.edit', $contract) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Editar</a>
                @endif
                @if (auth()->user()->hasPermission('excluir_contrato'))
                    <form method="POST" action="{{ route('contracts.destroy', $contract) }}" onsubmit="return confirm('Excluir este contrato?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Excluir</button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-semibold text-slate-950">Contrato</h2>
                @include('contracts.partials.refinancing-badge', ['refinancing' => $contract->refinancing])
            </div>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-slate-500">Cliente</dt><dd class="font-medium"><a href="{{ route('clients.show', $contract->client) }}" class="text-emerald-700">{{ $contract->client->name }}</a></dd></div>
                <div><dt class="text-slate-500">Vendedor</dt><dd class="font-medium">{{ $contract->client->user?->name ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Banco</dt><dd class="font-medium">{{ $contract->bankName() }}</dd></div>
                <div><dt class="text-slate-500">Convenio</dt><dd class="font-medium">{{ $contract->agreementName() }}</dd></div>
                <div><dt class="text-slate-500">Tipo</dt><dd class="font-medium">{{ $contract->contractTypeLabel() }}</dd></div>
                <div><dt class="text-slate-500">Valor contratado</dt><dd class="font-medium">R$ {{ number_format((float) $contract->contract_value, 2, ',', '.') }}</dd></div>
                <div><dt class="text-slate-500">Valor da parcela</dt><dd class="font-medium">R$ {{ number_format((float) $contract->installment_value, 2, ',', '.') }}</dd></div>
                <div><dt class="text-slate-500">Minimo para refinanciamento</dt><dd class="font-medium">{{ $contract->minimum_installments_for_refinancing }} parcelas</dd></div>
                <div><dt class="text-slate-500">Status do contrato</dt><dd class="font-medium">{{ ucfirst($contract->status) }}</dd></div>
            </dl>
            <p class="mt-5 rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $contract->refinancing['message'] }}</p>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="font-semibold text-slate-950">Registrar contato</h2>
            <form method="POST" action="{{ route('contracts.contact-history.store', $contract) }}" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="type">Tipo</label>
                    <select id="type" name="type" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        @foreach (\App\Models\ContactHistory::TYPES as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="contacted_at">Data do contato</label>
                    <input id="contacted_at" name="contacted_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700" for="description">Descricao</label>
                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"></textarea>
                </div>
                <div class="md:col-span-2">
                    <button class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Registrar</button>
                </div>
            </form>

            <div class="mt-8">
                <h3 class="font-semibold text-slate-950">Historico</h3>
                <div class="mt-3 divide-y divide-slate-100 rounded-md border border-slate-200">
                    @forelse ($contract->contactHistories as $history)
                        <div class="p-4 text-sm">
                            <div class="flex flex-col justify-between gap-1 sm:flex-row">
                                <p class="font-medium text-slate-900">{{ ucfirst($history->type) }}</p>
                                <p class="text-slate-500">{{ $history->contacted_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-slate-600">{{ $history->description ?: '-' }}</p>
                        </div>
                    @empty
                        <p class="p-4 text-sm text-slate-500">Nenhum contato registrado.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
