@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700" for="client_id">Cliente</label>
        <div class="relative mt-1">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
            </svg>
            <input type="search" data-select-search="client_id" placeholder="Buscar cliente por nome ou CPF" class="block w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </div>
        <select id="client_id" name="client_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <option value="">Selecione</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected((string) old('client_id', $contract->client_id ?? request('client_id')) === (string) $client->id)>{{ $client->name }} - {{ $client->cpf }}</option>
            @endforeach
        </select>
    </div>

    @php
        $selectedRegistrationId = old('client_registration_id', $contract->client_registration_id);
    @endphp
    <div id="client_registration_field" class="hidden md:col-span-2">
        <label class="block text-sm font-medium text-slate-700" for="client_registration_select">Matricula</label>
        <select id="client_registration_select" name="client_registration_id" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <option value="">Selecione</option>
        </select>
        <p id="client_registration_single" class="mt-1 hidden rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700"></p>
        <input id="client_registration_hidden" name="client_registration_id" type="hidden" value="{{ $selectedRegistrationId }}">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="bank_id">Banco</label>
        <select id="bank_id" name="bank_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <option value="">Selecione</option>
            @foreach ($banks as $bank)
                <option value="{{ $bank->id }}" @selected((string) old('bank_id', $contract->bank_id) === (string) $bank->id)>{{ $bank->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="agreement_id">Convenio</label>
        <select id="agreement_id" name="agreement_id" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <option value="">Selecione</option>
            @foreach ($agreements as $agreement)
                <option value="{{ $agreement->id }}" @selected((string) old('agreement_id', $contract->agreement_id) === (string) $agreement->id)>{{ $agreement->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="contract_type">Tipo do contrato</label>
        <select id="contract_type" name="contract_type" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('contract_type', $contract->contract_type ?? 'new') === $type)>
                    @switch($type)
                        @case('refinancing') Refinanciamento @break
                        @case('portability') Portabilidade @break
                        @default Novo
                    @endswitch
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="contract_value">Valor contratado</label>
        <input id="contract_value" name="contract_value" type="text" inputmode="numeric" data-money-mask value="{{ old('contract_value', $contract->contract_value) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="installment_value">Valor da parcela</label>
        <input id="installment_value" name="installment_value" type="text" inputmode="numeric" data-money-mask value="{{ old('installment_value', $contract->installment_value) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="total_installments">Total de parcelas</label>
        <input id="total_installments" name="total_installments" type="number" min="1" value="{{ old('total_installments', $contract->total_installments) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="paid_installments">Parcelas pagas</label>
        <input id="paid_installments" name="paid_installments" type="number" min="0" value="{{ old('paid_installments', $contract->paid_installments) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="minimum_installments_for_refinancing">Parcelas minimas para refinanciamento</label>
        <input id="minimum_installments_for_refinancing" name="minimum_installments_for_refinancing" type="number" min="1" value="{{ old('minimum_installments_for_refinancing', $contract->minimum_installments_for_refinancing) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="contract_date">Data do contrato</label>
        <input id="contract_date" name="contract_date" type="date" value="{{ old('contract_date', optional($contract->contract_date)->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="first_discount_date">Data do Primeiro Desconto</label>
        <input id="first_discount_date" name="first_discount_date" type="date" value="{{ old('first_discount_date', optional($contract->first_discount_date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('contracts.index') }}" class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
    <button class="inline-flex justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Salvar</button>
</div>

@php
    $clientRegistrations = $clients->mapWithKeys(fn ($client) => [
        $client->id => $client->registrations->map(fn ($registration) => [
            'id' => $registration->id,
            'number' => $registration->number,
        ])->values(),
    ]);
@endphp

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const contractDateInput = document.getElementById('contract_date');
        const paidInstallmentsInput = document.getElementById('paid_installments');
        const firstDiscountDateInput = document.getElementById('first_discount_date');
        const moneyInputs = document.querySelectorAll('[data-money-mask]');
        const clientInput = document.getElementById('client_id');
        const registrationField = document.getElementById('client_registration_field');
        const registrationSelect = document.getElementById('client_registration_select');
        const registrationSingle = document.getElementById('client_registration_single');
        const registrationHidden = document.getElementById('client_registration_hidden');
        const selectedRegistrationId = String(@json((string) $selectedRegistrationId));
        const clientRegistrations = @json($clientRegistrations);

        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        };

        const calculateFirstDiscountDate = () => {
            const contractDate = contractDateInput.value;
            const paidInstallments = Number.parseInt(paidInstallmentsInput.value || '0', 10);

            if (!contractDate || paidInstallments !== 0) {
                return;
            }

            const [year, month, day] = contractDate.split('-').map(Number);
            const monthsToAdd = day <= 15 ? 1 : 2;
            const firstDiscountDate = new Date(year, month - 1 + monthsToAdd, 5);

            firstDiscountDateInput.value = formatDate(firstDiscountDate);
        };

        contractDateInput.addEventListener('input', calculateFirstDiscountDate);
        paidInstallmentsInput.addEventListener('input', calculateFirstDiscountDate);
        calculateFirstDiscountDate();

        const formatCurrency = (value) => {
            const digits = value.replace(/\D/g, '');
            const amount = Number.parseInt(digits || '0', 10) / 100;

            return amount.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        };

        const normalizeInitialCurrency = (value) => {
            if (!value) {
                return '';
            }

            if (value.includes('R$') || value.includes(',')) {
                return formatCurrency(value);
            }

            const numericValue = Number.parseFloat(value);

            if (Number.isNaN(numericValue)) {
                return formatCurrency(value);
            }

            return numericValue.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        };

        moneyInputs.forEach((input) => {
            input.value = normalizeInitialCurrency(input.value);
            input.addEventListener('input', () => {
                input.value = formatCurrency(input.value);
            });
        });

        const updateRegistrationOptions = () => {
            const registrations = clientRegistrations[clientInput.value] || [];

            registrationSelect.innerHTML = '<option value="">Selecione</option>';
            registrations.forEach((registration) => {
                const option = document.createElement('option');
                option.value = registration.id;
                option.textContent = registration.number;
                option.selected = String(registration.id) === selectedRegistrationId;
                registrationSelect.appendChild(option);
            });

            if (registrations.length === 1) {
                registrationField.classList.remove('hidden');
                registrationSelect.classList.add('hidden');
                registrationSingle.classList.remove('hidden');
                registrationSingle.textContent = registrations[0].number;
                registrationSelect.disabled = true;
                registrationHidden.disabled = false;
                registrationHidden.value = registrations[0].id;
            } else if (registrations.length > 1) {
                registrationField.classList.remove('hidden');
                registrationSelect.classList.remove('hidden');
                registrationSingle.classList.add('hidden');
                registrationSingle.textContent = '';
                registrationSelect.disabled = false;
                registrationHidden.disabled = true;
                registrationHidden.value = '';

                if (!registrationSelect.value && selectedRegistrationId) {
                    registrationSelect.value = selectedRegistrationId;
                }
            } else {
                registrationField.classList.add('hidden');
                registrationSingle.classList.add('hidden');
                registrationSingle.textContent = '';
                registrationSelect.disabled = true;
                registrationHidden.disabled = false;
                registrationHidden.value = '';
            }
        };

        clientInput.addEventListener('change', updateRegistrationOptions);
        updateRegistrationOptions();
    });
</script>
