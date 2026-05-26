@csrf

<div>
    <label class="block text-sm font-medium text-slate-700" for="name">Nome do convenio</label>
    <input id="name" name="name" value="{{ old('name', $agreement->name) }}" required autofocus class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
</div>

<div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('agreements.index') }}" class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
    <button class="inline-flex justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Salvar</button>
</div>
