<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'permissions' => User::PERMISSIONS,
            'roles' => $this->roles(),
            'user' => new User(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validatedData($request));

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario cadastrado com sucesso.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'permissions' => User::PERMISSIONS,
            'roles' => $this->roles(),
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validatedData($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario atualizado com sucesso.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return redirect()
                ->route('users.index')
                ->withErrors('Voce nao pode excluir o proprio usuario.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario excluido com sucesso.');
    }

    /**
     * @return array<string, string>
     */
    private function roles(): array
    {
        return [
            'admin' => 'Administrador',
            'seller' => 'Vendedor',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(User::PERMISSIONS))],
        ]);

        $data['permissions'] = $data['permissions'] ?? [];

        return $data;
    }
}
