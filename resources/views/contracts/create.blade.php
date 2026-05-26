@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-950">Novo contrato</h1>
        <p class="mt-1 text-sm text-slate-600">Informe as parcelas minimas para calcular a elegibilidade dinamicamente.</p>
    </div>

    <form method="POST" action="{{ route('contracts.store') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @include('contracts.partials.form')
    </form>
@endsection
