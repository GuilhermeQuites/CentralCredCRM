<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
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
            'client' => new Client(),
            'users' => $this->sellers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Client::create($this->validatedData($request));

        return redirect()
            ->route('clients.index')
            ->with('status', 'Cliente cadastrado com sucesso.');
    }

    public function show(Client $client): View
    {
        $client->load(['user', 'contracts.bankRecord', 'contracts.agreement', 'contracts.contactHistories']);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', [
            'client' => $client,
            'users' => $this->sellers(),
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validatedData($request, $client));

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
        return $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => [
                'required',
                'string',
                'max:20',
                Rule::unique('clients', 'cpf')->ignore($client),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function sellers()
    {
        return User::query()->orderBy('name')->get();
    }
}
