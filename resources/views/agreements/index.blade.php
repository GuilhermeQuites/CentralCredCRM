@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950">Convenios</h1>
            <p class="mt-1 text-sm text-slate-600">Cadastro dos convenios usados nos contratos.</p>
        </div>
        @if (auth()->user()->hasPermission('criar_convenio'))
            <a href="{{ route('agreements.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Novo convenio</a>
        @endif
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Nome</th>
                    @if (auth()->user()->hasAnyPermission(['editar_convenio', 'excluir_convenio']))
                        <th class="px-5 py-3 text-right">Acoes</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($agreements as $agreement)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium text-slate-900">{{ $agreement->name }}</td>
                        @if (auth()->user()->hasAnyPermission(['editar_convenio', 'excluir_convenio']))
                            <td class="px-5 py-3 text-right">
                                @if (auth()->user()->hasPermission('editar_convenio'))
                                    <a href="{{ route('agreements.edit', $agreement) }}" class="font-medium text-emerald-700 hover:text-emerald-900">Editar</a>
                                @endif
                                @if (auth()->user()->hasPermission('excluir_convenio'))
                                    <form method="POST" action="{{ route('agreements.destroy', $agreement) }}" class="ml-3 inline" onsubmit="return confirm('Excluir este convenio?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-medium text-red-700 hover:text-red-900">Excluir</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-5 py-8 text-center text-slate-500">Nenhum convenio cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-200 px-5 py-4">{{ $agreements->links() }}</div>
    </div>
@endsection
