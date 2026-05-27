@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700" for="name">Nome</label>
        <input id="name" name="name" value="{{ old('name', $client->name) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="cpf">CPF</label>
        <input id="cpf" name="cpf" inputmode="numeric" maxlength="14" value="{{ old('cpf', $client->cpf) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="phone">Telefone</label>
        <input id="phone" name="phone" inputmode="numeric" maxlength="15" value="{{ old('phone', $client->phone) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="birth_date">Data de nascimento</label>
        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', optional($client->birth_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="user_id">Vendedor responsavel</label>
        <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <option value="">Sem responsavel</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('user_id', $client->user_id) === (string) $user->id)>{{ $user->name }} ({{ $user->role }})</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="email">E-mail</label>
        <input id="email" name="email" type="email" inputmode="email" autocomplete="email" value="{{ old('email', $client->email) }}" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    @php
        $registrationValues = old('registrations', $client->registrations?->pluck('number')->all() ?: ['']);
        $registrationCount = max(1, min(3, count(array_filter($registrationValues))));
    @endphp

    <div class="md:col-span-2 rounded-lg border border-slate-200 p-4">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div>
                <h2 class="font-semibold text-slate-950">Matriculas</h2>
                <p class="mt-1 text-sm text-slate-600">Cada cliente deve ter de 1 a 3 matriculas.</p>
            </div>
            <div class="flex flex-wrap gap-3 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="registration_count" value="1" @checked($registrationCount === 1) class="border-slate-300 text-emerald-700 focus:ring-emerald-500">
                    <span>1 matricula</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="registration_count" value="2" @checked($registrationCount === 2) class="border-slate-300 text-emerald-700 focus:ring-emerald-500">
                    <span>2 matriculas</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="registration_count" value="3" @checked($registrationCount === 3) class="border-slate-300 text-emerald-700 focus:ring-emerald-500">
                    <span>3 matriculas</span>
                </label>
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            @for ($index = 0; $index < 3; $index++)
                <div data-registration-field="{{ $index + 1 }}" class="{{ $index + 1 > $registrationCount ? 'hidden' : '' }}">
                    <label class="block text-sm font-medium text-slate-700" for="registration_{{ $index + 1 }}">Matricula {{ $index + 1 }}</label>
                    <input
                        id="registration_{{ $index + 1 }}"
                        name="registrations[]"
                        value="{{ $registrationValues[$index] ?? '' }}"
                        @required($index === 0)
                        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                    >
                </div>
            @endfor
        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const onlyDigits = (value) => value.replace(/\D/g, '');

        const maskCpf = (value) => onlyDigits(value)
            .slice(0, 11)
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2');

        const maskPhone = (value) => onlyDigits(value)
            .slice(0, 11)
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d{1,4})$/, '$1-$2');

        const cpfInput = document.getElementById('cpf');
        const phoneInput = document.getElementById('phone');

        cpfInput.value = maskCpf(cpfInput.value);
        phoneInput.value = maskPhone(phoneInput.value);

        cpfInput.addEventListener('input', () => {
            cpfInput.value = maskCpf(cpfInput.value);
        });

        phoneInput.addEventListener('input', () => {
            phoneInput.value = maskPhone(phoneInput.value);
        });

        const registrationRadios = document.querySelectorAll('input[name="registration_count"]');
        const registrationFields = document.querySelectorAll('[data-registration-field]');

        const updateRegistrationFields = () => {
            const selected = document.querySelector('input[name="registration_count"]:checked');
            const count = selected ? Number.parseInt(selected.value, 10) : 1;

            registrationFields.forEach((field) => {
                const fieldNumber = Number.parseInt(field.dataset.registrationField, 10);
                const input = field.querySelector('input');
                const visible = fieldNumber <= count;

                field.classList.toggle('hidden', !visible);
                input.disabled = !visible;
                input.required = visible;

                if (!visible) {
                    input.value = '';
                }
            });
        };

        registrationRadios.forEach((radio) => {
            radio.addEventListener('change', updateRegistrationFields);
        });

        updateRegistrationFields();
    });
</script>
