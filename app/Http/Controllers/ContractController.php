<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Bank;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Contract;
use App\Services\FirstDiscountDateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(): View
    {
        $search = request('search');

        return view('contracts.index', [
            'contracts' => Contract::with(['client', 'clientRegistration', 'bankRecord', 'agreement'])
                ->when($search, function ($query, string $search): void {
                    $query->whereHas('client', function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('cpf', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('contracts.create', [
            'contract' => new Contract(),
            'clients' => $this->clients(),
            'banks' => $this->banks(),
            'agreements' => $this->agreements(),
            'types' => Contract::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Contract::create($this->validatedData($request));

        return redirect()
            ->route('contracts.index')
            ->with('status', 'Contrato cadastrado com sucesso.');
    }

    public function show(Contract $contract): View
    {
        $contract->load(['client.user', 'clientRegistration', 'bankRecord', 'agreement', 'contactHistories']);
        $contract->refinancing = $contract->refinancingStatus();

        return view('contracts.show', compact('contract'));
    }

    public function edit(Contract $contract): View
    {
        return view('contracts.edit', [
            'contract' => $contract,
            'clients' => $this->clients(),
            'banks' => $this->banks(),
            'agreements' => $this->agreements(),
            'types' => Contract::TYPES,
        ]);
    }

    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $contract->update($this->validatedData($request));

        return redirect()
            ->route('contracts.show', $contract)
            ->with('status', 'Contrato atualizado com sucesso.');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $contract->delete();

        return redirect()
            ->route('contracts.index')
            ->with('status', 'Contrato excluido com sucesso.');
    }

    public function refinancing(Request $request): View
    {
        $filter = $request->string('filter')->toString() ?: 'eligible';
        $contracts = Contract::with(['client.user', 'clientRegistration', 'bankRecord', 'agreement'])
            ->where('status', 'active')
            ->orderBy('paid_installments', 'desc')
            ->get()
            ->map(function (Contract $contract) {
                $contract->refinancing = $contract->refinancingStatus();

                return $contract;
            })
            ->filter(function (Contract $contract) use ($filter) {
                return match ($filter) {
                    'waiting' => $contract->refinancing['status'] === 'waiting',
                    'up_to_3' => $contract->refinancing['status'] === 'waiting'
                        && $contract->refinancing['remaining_installments'] <= 3,
                    'up_to_6' => $contract->refinancing['status'] === 'waiting'
                        && $contract->refinancing['remaining_installments'] <= 6,
                    default => $contract->refinancing['status'] === 'eligible',
                };
            })
            ->values();

        return view('contracts.refinancing', compact('contracts', 'filter'));
    }

    private function validatedData(Request $request): array
    {
        $request->merge([
            'contract_value' => $this->normalizeCurrency($request->input('contract_value')),
            'installment_value' => $this->normalizeCurrency($request->input('installment_value')),
        ]);

        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'client_registration_id' => ['nullable', 'exists:client_registrations,id'],
            'bank_id' => ['required', 'exists:banks,id'],
            'agreement_id' => ['required', 'exists:agreements,id'],
            'contract_type' => ['required', Rule::in(Contract::TYPES)],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'installment_value' => ['required', 'numeric', 'min:0'],
            'total_installments' => ['required', 'integer', 'min:1'],
            'paid_installments' => ['required', 'integer', 'min:0', 'lte:total_installments'],
            'minimum_installments_for_refinancing' => ['required', 'integer', 'min:1', 'lte:total_installments'],
            'contract_date' => ['required', 'date'],
            'first_discount_date' => ['nullable', 'date'],
        ]);

        $data['client_registration_id'] = $data['client_registration_id'] ?? null;

        $clientRegistrations = ClientRegistration::query()
            ->where('client_id', $data['client_id'])
            ->orderBy('id')
            ->get();

        if ($clientRegistrations->count() === 1 && blank($data['client_registration_id'])) {
            $data['client_registration_id'] = $clientRegistrations->first()->id;
        }

        if (! $clientRegistrations->contains('id', (int) $data['client_registration_id'])) {
            throw ValidationException::withMessages([
                'client_registration_id' => 'Selecione uma matricula valida para o cliente informado.',
            ]);
        }

        if ((int) $data['paid_installments'] === 0) {
            $expectedDate = app(FirstDiscountDateService::class)
                ->calculate($data['contract_date'])
                ->toDateString();

            if (blank($data['first_discount_date'])) {
                $data['first_discount_date'] = $expectedDate;
            }

            if ($data['first_discount_date'] !== $expectedDate) {
                throw ValidationException::withMessages([
                    'first_discount_date' => 'A data do primeiro desconto deve seguir a regra de fechamento da folha no dia 15.',
                ]);
            }
        }

        $data['bank'] = Bank::find($data['bank_id'])->name;

        return $data;
    }

    private function normalizeCurrency(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        if (str_contains($value, ',') || str_contains($value, 'R$')) {
            $value = preg_replace('/[^\d,]/', '', $value) ?? '';
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }

    private function clients()
    {
        return Client::query()
            ->with('registrations')
            ->orderBy('name')
            ->get();
    }

    private function banks()
    {
        return Bank::query()->orderBy('name')->get();
    }

    private function agreements()
    {
        return Agreement::query()->orderBy('name')->get();
    }
}
