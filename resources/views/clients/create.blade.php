@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-950">Novo cliente</h1>
        <p class="mt-1 text-sm text-slate-600">Cadastre os dados do cliente e o vendedor responsavel.</p>
    </div>

    <form method="POST" action="{{ route('clients.store') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @include('clients.partials.form')
    </form>
@endsection
