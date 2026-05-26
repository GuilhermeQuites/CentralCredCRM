@extends('layouts.app')

@section('content')
    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-950">Central Cred</h1>
                <p class="mt-1 text-sm text-slate-600">Acesse o CRM de acompanhamento de clientes.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                @csrf

                <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">

                <label class="mt-4 block text-sm font-medium text-slate-700" for="password">Senha</label>
                <input id="password" name="password" type="password" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">

                <label class="mt-4 flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Manter conectado
                </label>

                <button class="mt-6 w-full rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Entrar</button>
            </form>
        </div>
    </main>
@endsection
