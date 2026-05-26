@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">Clientes</h1>
            <p class="mt-1 text-sm text-slate-600">Carteira de clientes cadastrados.</p>
        </div>
        <a href="{{ route('clients.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Novo cliente</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('clients.index') }}" class="border-b border-slate-200 p-4">
            <div class="relative max-w-md">
                <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input name="search" type="search" value="{{ $search }}" placeholder="Buscar por nome ou CPF" class="block w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Nome</th>
                        <th class="px-5 py-3">CPF</th>
                        <th class="px-5 py-3">Telefone</th>
                        <th class="px-5 py-3">Vendedor</th>
                        <th class="px-5 py-3 text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $client->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $client->cpf }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $client->phone }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $client->user?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('clients.show', $client) }}" class="font-medium text-emerald-700 hover:text-emerald-900">Visualizar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">Nenhum cliente encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-5 py-4">{{ $clients->links() }}</div>
    </div>
@endsection
