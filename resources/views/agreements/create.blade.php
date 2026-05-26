@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-950">Novo convenio</h1>
    </div>

    <form method="POST" action="{{ route('agreements.store') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @include('agreements.partials.form')
    </form>
@endsection
