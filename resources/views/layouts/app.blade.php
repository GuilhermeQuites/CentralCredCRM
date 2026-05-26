<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Central Cred') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    @auth
        <div class="min-h-screen lg:flex">
            <aside class="border-b border-slate-200 bg-white lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-r">
                <div class="flex items-center justify-between px-5 py-4 lg:block">
                    <a href="{{ route('dashboard') }}" class="block">
                        <span class="text-lg font-semibold text-emerald-700">Central Cred</span>
                        <span class="block text-xs text-slate-500">CRM Consignado</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                        @csrf
                        <button class="rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700">Sair</button>
                    </form>
                </div>

                @php($authUser = auth()->user())
                <nav class="flex gap-2 overflow-x-auto px-5 pb-4 text-sm lg:block lg:space-y-1">
                    <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Dashboard</a>
                    <a href="{{ route('clients.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('clients.*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Clientes</a>
                    <a href="{{ route('contracts.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('contracts.index', 'contracts.create', 'contracts.edit', 'contracts.show') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Contratos</a>
                    @if ($authUser->hasPermission('visualizar_bancos'))
                        <a href="{{ route('banks.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('banks.*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Bancos</a>
                    @endif
                    @if ($authUser->hasPermission('visualizar_convenios'))
                        <a href="{{ route('agreements.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('agreements.*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Convenios</a>
                    @endif
                    <a href="{{ route('contracts.refinancing') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('contracts.refinancing') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Fila</a>
                    @if ($authUser->hasPermission('visualizar_usuarios'))
                        <a href="{{ route('users.index') }}" class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('users.*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">Usuarios</a>
                    @endif
                </nav>

                <div class="mt-auto hidden border-t border-slate-200 p-5 lg:block">
                    <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                    <p class="mb-3 text-xs uppercase tracking-wide text-slate-500">{{ auth()->user()->role }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Sair</button>
                    </form>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <p class="font-medium">Revise os campos abaixo.</p>
                            <ul class="mt-2 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    @else
        @yield('content')
    @endauth
</body>
</html>
