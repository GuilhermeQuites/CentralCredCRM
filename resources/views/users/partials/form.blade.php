<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700" for="name">Nome</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="email">E-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="password">Senha</label>
        <input id="password" name="password" type="password" {{ $user->exists ? '' : 'required' }} class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        @if ($user->exists)
            <p class="mt-1 text-xs text-slate-500">Deixe em branco para manter a senha atual.</p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="role">Perfil</label>
        <select id="role" name="role" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role ?: 'seller') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mt-6">
    <div class="mb-3">
        <h2 class="font-semibold text-slate-950">Permissoes</h2>
        <p class="mt-1 text-sm text-slate-600">Usuarios administradores sempre possuem acesso total.</p>
    </div>

    @php($selectedPermissions = old('permissions', $user->permissions ?? []))
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($permissions as $permission => $label)
            <label class="flex items-center gap-3 rounded-md border border-slate-200 px-3 py-2 text-sm">
                <input
                    type="checkbox"
                    name="permissions[]"
                    value="{{ $permission }}"
                    @checked(in_array($permission, $selectedPermissions, true))
                    class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-500"
                >
                <span class="font-medium text-slate-700">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>
