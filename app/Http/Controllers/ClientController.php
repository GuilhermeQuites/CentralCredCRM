<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $search = request('search');

        return view('clients.index', [
            'clients' => Client::with('user')
                ->when($search, function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
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
        return view('clients.create', [
            'client' => new Client(['user_id' => auth()->id()]),
            'users' => $this->sellers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data): void {
            $client = Client::create($data['client']);
            $this->syncRegistrations($client, $data['registrations']);
        });

        return redirect()
            ->route('clients.index')
            ->with('status', 'Cliente cadastrado com sucesso.');
    }

    public function show(Client $client): View
    {
        $client->load(['user', 'registrations', 'contracts.bankRecord', 'contracts.agreement', 'contracts.clientRegistration', 'contracts.contactHistories']);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        $client->load('registrations');

        return view('clients.edit', [
            'client' => $client,
            'users' => $this->sellers(),
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $this->validatedData($request, $client);

        DB::transaction(function () use ($client, $data): void {
            $client->update($data['client']);
            $this->syncRegistrations($client, $data['registrations']);
        });

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('status', 'Cliente excluido com sucesso.');
    }

    private function validatedData(Request $request, ?Client $client = null): array
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
            'cpf' => [
                'required',
                'string',
                'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                Rule::unique('clients', 'cpf')->ignore($client),
            ],
            'phone' => ['required', 'string', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'registration_count' => ['nullable', Rule::in(['1', '2', '3'])],
            'registrations' => ['required', 'array', 'size:' . $registrationCount],
            'registrations.*' => ['required', 'string', 'max:255', 'distinct'],
        ], [
            'cpf.required' => 'CPF e obrigatorio.',
            'cpf.regex' => 'CPF deve estar no formato 000.000.000-00.',
            'cpf.unique' => 'CPF ja possui cadastro.',
            'phone.required' => 'Telefone e obrigatorio.',
            'phone.regex' => 'Telefone deve estar no formato (00) 00000-0000.',
            'email.email' => 'E-mail deve ser um endereco valido.',
            'registrations.required' => 'Informe ao menos uma matricula.',
            'registrations.size' => 'Preencha a quantidade de matriculas selecionada.',
            'registrations.*.required' => 'Matricula e obrigatoria.',
            'registrations.*.distinct' => 'Nao repita a mesma matricula para o cliente.',
        ]);

        return [
            'client' => collect($data)->except('registration_count', 'registrations')->all(),
            'registrations' => $data['registrations'],
        ];
    }

    private function formatCpf(string $value): string
    {
        $digits = substr(preg_replace('/\D/', '', $value) ?? '', 0, 11);

        if (strlen($digits) !== 11) {
            return $value;
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 3),
            substr($digits, 9, 2),
        );
    }

    private function formatPhone(string $value): string
    {
        $digits = substr(preg_replace('/\D/', '', $value) ?? '', 0, 11);

        if (strlen($digits) !== 11) {
            return $value;
        }

        return sprintf(
            '(%s) %s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 5),
            substr($digits, 7, 4),
        );
    }

    private function sellers()
    {
        return User::query()->orderBy('name')->get();
    }

    /**
     * @param array<int, string> $registrations
     */
    private function syncRegistrations(Client $client, array $registrations): void
    {
        $numbers = collect($registrations)
            ->map(fn (string $registration) => trim($registration))
            ->filter()
            ->values();

        $existingRegistrations = $client->registrations()->orderBy('id')->get();

        $numbers->each(function (string $number, int $index) use ($client, $existingRegistrations): void {
            $registration = $existingRegistrations->get($index);

            if ($registration) {
                $registration->update(['number' => $number]);

                return;
            }

            ClientRegistration::create([
                'client_id' => $client->id,
                'number' => $number,
            ]);
        });

        $existingRegistrations
            ->slice($numbers->count())
            ->each(fn (ClientRegistration $registration) => $registration->delete());
    }
}
