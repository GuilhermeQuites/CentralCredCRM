<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

                @php
                    $authUser = auth()->user();
                    $initialRefinancingNotificationCount = count(app(\App\Services\RefinancingNotificationService::class)->activePayload());
                @endphp
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
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="mb-3 text-xs uppercase tracking-wide text-slate-500">{{ auth()->user()->role }}</p>
                        </div>
                        <button data-refinancing-notification-toggle type="button" class="relative shrink-0 rounded-md border border-slate-200 bg-white p-2.5 text-slate-600 hover:bg-slate-50" aria-label="Notificacoes de refinanciamento">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 8h18s-3-1-3-8"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span data-refinancing-notification-count style="{{ $initialRefinancingNotificationCount > 0 ? 'display:block;' : 'display:none;' }}position:absolute;top:3px;right:5px;color:#dc2626;font-size:12px;font-weight:800;line-height:1;">{{ $initialRefinancingNotificationCount }}</span>
                        </button>
                    </div>
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

        <div id="refinancing-notification-popover" class="fixed z-50 hidden w-[min(380px,calc(100vw-2rem))] rounded-lg border border-slate-200 bg-white shadow-xl">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="font-semibold text-slate-950">Refinanciamentos</p>
                <p class="text-xs text-slate-500">Contratos prontos para refinanciar.</p>
            </div>
            <div id="refinancing-notification-list" class="max-h-96 overflow-y-auto px-5 py-3"></div>
        </div>

        <button data-refinancing-notification-toggle type="button" class="fixed bottom-4 left-4 z-40 rounded-full border border-slate-200 bg-white p-3 text-slate-700 shadow-lg hover:bg-slate-50 lg:hidden" aria-label="Notificacoes de refinanciamento">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 8h18s-3-1-3-8"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span data-refinancing-notification-count style="{{ $initialRefinancingNotificationCount > 0 ? 'display:block;' : 'display:none;' }}position:absolute;top:3px;right:5px;color:#dc2626;font-size:12px;font-weight:800;line-height:1;">{{ $initialRefinancingNotificationCount }}</span>
        </button>
    @else
        @yield('content')
    @endauth
    @auth
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const buttons = document.querySelectorAll('[data-refinancing-notification-toggle]');
                const badges = document.querySelectorAll('[data-refinancing-notification-count]');
                const popover = document.getElementById('refinancing-notification-popover');
                const list = document.getElementById('refinancing-notification-list');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                let notifications = [];

                const request = async (url, body = null) => {
                    const response = await fetch(url, {
                        method: body ? 'POST' : 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: body ? JSON.stringify(body) : null,
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao carregar notificacoes.');
                    }

                    return response.json();
                };

                const render = (payload) => {
                    notifications = payload.notifications || [];
                    const count = payload.count || 0;

                    badges.forEach((badge) => {
                        badge.textContent = count;
                        badge.style.display = count === 0 ? 'none' : 'block';
                        badge.style.color = '#fff';
                        badge.style.background = 'transparent';
                        badge.style.color = '#dc2626';
                    });

                    if (payload.show_login_alert) {
                        window.alert('Voce possui contratos disponiveis para refinanciamento.');
                    }

                    if (count === 0) {
                        list.innerHTML = '<p class="text-sm text-slate-500">Nenhuma notificacao pendente.</p>';
                        return;
                    }

                    list.innerHTML = notifications.map((notification) => `
                        <article class="border-b border-slate-100 py-4 last:border-b-0">
                            <p class="text-sm text-slate-700">
                                <span class="font-semibold text-slate-950">${notification.client_name}</span>
                                tem contrato pronto para
                                <a href="${notification.show_url}" class="font-semibold text-emerald-700 hover:text-emerald-900">refinanciar</a>.
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Matricula ${notification.registration} | ${notification.bank}</p>
                        </article>
                    `).join('');
                };

                const refresh = async () => {
                    render(await request('{{ route('refinancing-notifications.index') }}'));
                };

                buttons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const rect = button.getBoundingClientRect();
                        popover.style.left = `${Math.max(16, rect.left)}px`;
                        popover.style.top = `${rect.bottom + 8}px`;
                        popover.classList.toggle('hidden');
                        refresh();
                    });
                });

                document.addEventListener('click', (event) => {
                    if (
                        !popover.contains(event.target)
                        && !event.target.closest('[data-refinancing-notification-toggle]')
                    ) {
                        popover.classList.add('hidden');
                    }
                });

                refresh();
            });
        </script>
    @endauth
</body>
</html>
