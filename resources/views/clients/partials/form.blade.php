@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700" for="name">Nome</label>
        <input id="name" name="name" value="{{ old('name', $client->name) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="cpf">CPF</label>
        <input id="cpf" name="cpf" value="{{ old('cpf', $client->cpf) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="phone">Telefone</label>
        <input id="phone" name="phone" value="{{ old('phone', $client->phone) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="birth_date">Data de nascimento</label>
        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', optional($client->birth_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700" for="user_id">Vendedor responsavel</label>
        <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <option value="">Sem responsavel</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('user_id', $client->user_id) === (string) $user->id)>{{ $user->name }} ({{ $user->role }})</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700" for="notes">Observacoes</label>
        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">{{ old('notes', $client->notes) }}</textarea>
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('clients.index') }}" class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
    <button class="inline-flex justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Salvar</button>
</div>
