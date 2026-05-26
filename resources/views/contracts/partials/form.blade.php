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
        <input id="contract_value" name="contract_value" type="number" step="0.01" min="0" value="{{ old('contract_value', $contract->contract_value) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="installment_value">Valor da parcela</label>
        <input id="installment_value" name="installment_value" type="number" step="0.01" min="0" value="{{ old('installment_value', $contract->installment_value) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
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
        <label class="block text-sm font-medium text-slate-700" for="status">Status</label>
        <select id="status" name="status" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $contract->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="contract_date">Data do contrato</label>
        <input id="contract_date" name="contract_date" type="date" value="{{ old('contract_date', optional($contract->contract_date)->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('contracts.index') }}" class="inline-flex justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
    <button class="inline-flex justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Salvar</button>
</div>
