<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\Bank;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\ContactHistory;
use App\Models\Contract;
use App\Models\User;
use App\Services\FirstDiscountDateService;
use App\Services\RefinancingNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CentralCredApiController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'clients_count' => Client::count(),
            'contracts_count' => Contract::count(),
            'eligible_refinancing_count' => Contract::query()
                ->where('status', 'active')
                ->whereColumn('paid_installments', '>=', 'minimum_installments_for_refinancing')
                ->count(),
            'active_notifications_count' => app(RefinancingNotificationService::class)->activeContracts()->count(),
        ]);
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'users' => User::orderBy('name')->get(),
            'clients' => Client::with('registrations')->orderBy('name')->get(),
            'banks' => Bank::orderBy('name')->get(),
            'agreements' => Agreement::orderBy('name')->get(),
            'contract_types' => Contract::TYPES,
            'contract_statuses' => Contract::STATUSES,
            'contact_types' => ContactHistory::TYPES,
            'user_permissions' => User::PERMISSIONS,
            'user_roles' => ['admin', 'seller'],
        ]);
    }

    public function clients(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();

        return response()->json(Client::with(['user', 'registrations'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('cpf', 'like', "%{$search}%")))
            ->latest()
            ->paginate($request->integer('per_page', 15)));
    }

    public function storeClient(Request $request): JsonResponse
    {
        $data = $this->validatedClient($request);

        $client = DB::transaction(function () use ($data): Client {
            $client = Client::create($data['client']);
            $this->syncRegistrations($client, $data['registrations']);

            return $client->load(['user', 'registrations']);
        });

        return response()->json($client, 201);
    }

    public function showClient(Client $client): JsonResponse
    {
        return response()->json($client->load(['user', 'registrations', 'contracts.bankRecord', 'contracts.agreement', 'contracts.clientRegistration']));
    }

    public function updateClient(Request $request, Client $client): JsonResponse
    {
        $this->ensurePermission($request, 'editar_cliente');
        $data = $this->validatedClient($request, $client);

        $client = DB::transaction(function () use ($client, $data): Client {
            $client->update($data['client']);
            $this->syncRegistrations($client, $data['registrations']);

            return $client->refresh()->load(['user', 'registrations']);
        });

        return response()->json($client);
    }

    public function destroyClient(Request $request, Client $client): JsonResponse
    {
        $this->ensurePermission($request, 'excluir_cliente');
        $client->delete();

        return response()->json(['message' => 'Cliente excluido com sucesso.']);
    }

    public function contracts(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();

        return response()->json(Contract::with(['client', 'clientRegistration', 'bankRecord', 'agreement'])
            ->when($search, fn ($query) => $query->whereHas('client', fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('cpf', 'like', "%{$search}%")))
            ->latest()
            ->paginate($request->integer('per_page', 15)));
    }

    public function storeContract(Request $request): JsonResponse
    {
        $contract = Contract::create($this->validatedContract($request))
            ->load(['client', 'clientRegistration', 'bankRecord', 'agreement']);

        return response()->json($contract, 201);
    }

    public function showContract(Contract $contract): JsonResponse
    {
        $contract->load(['client.user', 'clientRegistration', 'bankRecord', 'agreement', 'contactHistories']);
        $contract->refinancing = $contract->refinancingStatus();

        return response()->json($contract);
    }

    public function updateContract(Request $request, Contract $contract): JsonResponse
    {
        $this->ensurePermission($request, 'editar_contrato');
        $contract->update($this->validatedContract($request));

        return response()->json($contract->refresh()->load(['client', 'clientRegistration', 'bankRecord', 'agreement']));
    }

    public function destroyContract(Request $request, Contract $contract): JsonResponse
    {
        $this->ensurePermission($request, 'excluir_contrato');
        $contract->delete();

        return response()->json(['message' => 'Contrato excluido com sucesso.']);
    }

    public function refinancingQueue(Request $request): JsonResponse
    {
        $filter = $request->string('filter')->toString() ?: 'eligible';
        $contracts = Contract::with(['client.user', 'clientRegistration', 'bankRecord', 'agreement'])
            ->where('status', 'active')
            ->orderByDesc('paid_installments')
            ->get()
            ->map(function (Contract $contract) {
                $contract->refinancing = $contract->refinancingStatus();

                return $contract;
            })
            ->filter(fn (Contract $contract) => match ($filter) {
                'waiting' => $contract->refinancing['status'] === 'waiting',
                'up_to_3' => $contract->refinancing['status'] === 'waiting' && $contract->refinancing['remaining_installments'] <= 3,
                'up_to_6' => $contract->refinancing['status'] === 'waiting' && $contract->refinancing['remaining_installments'] <= 6,
                default => $contract->refinancing['status'] === 'eligible',
            })
            ->values();

        return response()->json(['data' => $contracts]);
    }

    public function storeContactHistory(Request $request, Contract $contract): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(ContactHistory::TYPES)],
            'description' => ['nullable', 'string'],
            'contacted_at' => ['required', 'date'],
        ]);

        return response()->json($contract->contactHistories()->create($data), 201);
    }

    public function banks(): JsonResponse
    {
        return response()->json(['data' => Bank::orderBy('name')->get()]);
    }

    public function storeBank(Request $request): JsonResponse
    {
        $this->ensurePermission($request, 'criar_bancos');

        return response()->json(Bank::create($this->validatedName($request, 'banks')), 201);
    }

    public function updateBank(Request $request, Bank $bank): JsonResponse
    {
        $this->ensurePermission($request, 'editar_bancos');
        $bank->update($this->validatedName($request, 'banks', $bank->id));

        return response()->json($bank);
    }

    public function destroyBank(Request $request, Bank $bank): JsonResponse
    {
        $this->ensurePermission($request, 'excluir_bancos');
        $bank->delete();

        return response()->json(['message' => 'Banco excluido com sucesso.']);
    }

    public function agreements(): JsonResponse
    {
        return response()->json(['data' => Agreement::orderBy('name')->get()]);
    }

    public function storeAgreement(Request $request): JsonResponse
    {
        $this->ensurePermission($request, 'criar_convenio');

        return response()->json(Agreement::create($this->validatedName($request, 'agreements')), 201);
    }

    public function updateAgreement(Request $request, Agreement $agreement): JsonResponse
    {
        $this->ensurePermission($request, 'editar_convenio');
        $agreement->update($this->validatedName($request, 'agreements', $agreement->id));

        return response()->json($agreement);
    }

    public function destroyAgreement(Request $request, Agreement $agreement): JsonResponse
    {
        $this->ensurePermission($request, 'excluir_convenio');
        $agreement->delete();

        return response()->json(['message' => 'Convenio excluido com sucesso.']);
    }

    public function users(Request $request): JsonResponse
    {
        $this->ensurePermission($request, 'visualizar_usuarios');

        return response()->json(User::orderBy('name')->paginate($request->integer('per_page', 15)));
    }

    public function storeUser(Request $request): JsonResponse
    {
        $this->ensurePermission($request, 'criar_usuarios');

        return response()->json(User::create($this->validatedUser($request)), 201);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        $this->ensurePermission($request, 'editar_usuarios');
        $data = $this->validatedUser($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroyUser(Request $request, User $user): JsonResponse
    {
        $this->ensurePermission($request, 'excluir_usuarios');

        if ($request->user()->is($user)) {
            return response()->json(['message' => 'Voce nao pode excluir o proprio usuario.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario excluido com sucesso.']);
    }

    public function notifications(RefinancingNotificationService $service): JsonResponse
    {
        $notifications = $service->activePayload();

        return response()->json(['count' => count($notifications), 'notifications' => $notifications]);
    }

    public function markNotificationViewed(Contract $contract, RefinancingNotificationService $service): JsonResponse
    {
        $service->markViewed($contract);

        return $this->notifications($service);
    }

    public function markNotificationNotRefinanced(Request $request, Contract $contract, RefinancingNotificationService $service): JsonResponse
    {
        $data = $request->validate([
            'notify_after_paid_installments' => ['required', 'integer', 'min:' . ($contract->paid_installments + 1)],
        ]);

        $service->markNotRefinanced($contract, (int) $data['notify_after_paid_installments']);

        return $this->notifications($service);
    }

    private function ensurePermission(Request $request, string $permission): void
    {
        if (! $request->user()->hasPermission($permission)) {
            throw new HttpResponseException(response()->json([
                'message' => 'Sem permissao para executar esta acao.',
            ], 403));
        }
    }

    private function validatedName(Request $request, string $table, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique($table, 'name')->ignore($ignoreId)],
        ]);
    }

    private function validatedUser(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'seller'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(User::PERMISSIONS))],
        ]);

        $data['permissions'] = $data['permissions'] ?? [];

        return $data;
    }

    private function validatedClient(Request $request, ?Client $client = null): array
    {
        $request->merge([
            'cpf' => $this->formatCpf($request->string('cpf')->toString()),
            'phone' => $this->formatPhone($request->string('phone')->toString()),
        ]);

        $registrationCount = in_array($request->input('registration_count'), ['1', '2', '3'], true)
            ? (int) $request->input('registration_count')
            : 1;

        $request->merge([
            'registrations' => collect($request->input('registrations', []))
                ->take($registrationCount)
                ->map(fn (mixed $registration) => is_string($registration) ? trim($registration) : $registration)
                ->all(),
        ]);

        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', Rule::unique('clients', 'cpf')->ignore($client)],
            'phone' => ['required', 'string', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'registration_count' => ['nullable', Rule::in(['1', '2', '3'])],
            'registrations' => ['required', 'array', 'size:' . $registrationCount],
            'registrations.*' => ['required', 'string', 'max:255', 'distinct'],
        ], [
            'cpf.unique' => 'CPF ja possui cadastro.',
        ]);

        return [
            'client' => collect($data)->except('registration_count', 'registrations')->all(),
            'registrations' => $data['registrations'],
        ];
    }

    private function validatedContract(Request $request): array
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
        $registrations = ClientRegistration::query()->where('client_id', $data['client_id'])->orderBy('id')->get();

        if ($registrations->count() === 1 && blank($data['client_registration_id'])) {
            $data['client_registration_id'] = $registrations->first()->id;
        }

        if (! $registrations->contains('id', (int) $data['client_registration_id'])) {
            throw ValidationException::withMessages([
                'client_registration_id' => 'Selecione uma matricula valida para o cliente informado.',
            ]);
        }

        if ((int) $data['paid_installments'] === 0) {
            $expectedDate = app(FirstDiscountDateService::class)->calculate($data['contract_date'])->toDateString();
            $data['first_discount_date'] = $data['first_discount_date'] ?: $expectedDate;

            if ($data['first_discount_date'] !== $expectedDate) {
                throw ValidationException::withMessages([
                    'first_discount_date' => 'A data do primeiro desconto deve seguir a regra de fechamento da folha no dia 15.',
                ]);
            }
        }

        $data['bank'] = Bank::find($data['bank_id'])->name;

        return $data;
    }

    private function syncRegistrations(Client $client, array $registrations): void
    {
        $numbers = collect($registrations)->map(fn (string $registration) => trim($registration))->filter()->values();
        $existing = $client->registrations()->orderBy('id')->get();

        $numbers->each(function (string $number, int $index) use ($client, $existing): void {
            $registration = $existing->get($index);

            if ($registration) {
                $registration->update(['number' => $number]);
                return;
            }

            ClientRegistration::create(['client_id' => $client->id, 'number' => $number]);
        });

        $existing->slice($numbers->count())->each(fn (ClientRegistration $registration) => $registration->delete());
    }

    private function normalizeCurrency(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($value !== '' && (str_contains($value, ',') || str_contains($value, 'R$'))) {
            $value = str_replace(',', '.', preg_replace('/[^\d,]/', '', $value) ?? '');
        }

        return $value;
    }

    private function formatCpf(string $value): string
    {
        $digits = substr(preg_replace('/\D/', '', $value) ?? '', 0, 11);
        return strlen($digits) === 11
            ? sprintf('%s.%s.%s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6, 3), substr($digits, 9, 2))
            : $value;
    }

    private function formatPhone(string $value): string
    {
        $digits = substr(preg_replace('/\D/', '', $value) ?? '', 0, 11);
        return strlen($digits) === 11
            ? sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4))
            : $value;
    }
}
