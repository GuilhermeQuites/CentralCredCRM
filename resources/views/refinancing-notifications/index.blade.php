@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">Notificacoes de refinanciamento</h1>
            <p class="text-sm text-slate-500">Contratos ativos que estao disponiveis para refinanciar.</p>
        </div>
        <a href="{{ route('contracts.refinancing') }}" class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ver fila</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        @if (count($notifications) === 0)
            <div class="px-5 py-8 text-center">
                <p class="font-medium text-slate-700">Nenhuma notificacao pendente.</p>
                <p class="mt-1 text-sm text-slate-500">Quando um contrato ficar disponivel, ele vai aparecer aqui.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($notifications as $notification)
                    <article class="px-5 py-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-slate-700">
                                    <span class="font-semibold text-slate-950">{{ $notification['client_name'] }}</span>
                                    tem contrato pronto para
                                    <a href="{{ $notification['show_url'] }}" class="font-semibold text-emerald-700 hover:text-emerald-900">refinanciar</a>.
                                </p>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                    <span>Matricula {{ $notification['registration'] }}</span>
                                    <span>{{ $notification['bank'] }}</span>
                                    <span>{{ $notification['paid_installments'] }} parcelas pagas</span>
                                    <span>Parcela {{ $notification['installment_value'] }}</span>
                                </div>
                            </div>
                            <a href="{{ $notification['show_url'] }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Visualizar</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
