@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-950">Editar usuario</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $user->name }}</p>
    </div>

    <form method="POST" action="{{ route('users.update', $user) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')
        @include('users.partials.form')

        <div class="mt-6 flex justify-end gap-2">
            <a href="{{ route('users.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
            <button class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Salvar</button>
        </div>
    </form>
@endsection
