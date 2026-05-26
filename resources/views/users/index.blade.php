@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">Usuarios</h1>
            <p class="mt-1 text-sm text-slate-600">Controle de acesso e permissoes do sistema.</p>
        </div>
        @if (auth()->user()->hasPermission('criar_usuarios'))
            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Novo usuario</a>
        @endif
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Nome</th>
                    <th class="px-5 py-3">E-mail</th>
                    <th class="px-5 py-3">Perfil</th>
                    @if (auth()->user()->hasAnyPermission(['editar_usuarios', 'excluir_usuarios']))
                        <th class="px-5 py-3 text-right">Acoes</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $user->role === 'admin' ? 'Administrador' : 'Vendedor' }}</td>
                        @if (auth()->user()->hasAnyPermission(['editar_usuarios', 'excluir_usuarios']))
                            <td class="px-5 py-3 text-right">
                                @if (auth()->user()->hasPermission('editar_usuarios'))
                                    <a href="{{ route('users.edit', $user) }}" class="font-medium text-emerald-700 hover:text-emerald-900">Editar</a>
                                @endif
                                @if (auth()->user()->hasPermission('excluir_usuarios') && ! auth()->user()->is($user))
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="ml-3 inline" onsubmit="return confirm('Excluir este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-medium text-red-700 hover:text-red-900">Excluir</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Nenhum usuario cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-200 px-5 py-4">{{ $users->links() }}</div>
    </div>
@endsection
