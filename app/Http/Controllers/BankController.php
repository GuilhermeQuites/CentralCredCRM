<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankController extends Controller
{
    public function index(): View
    {
        return view('banks.index', [
            'banks' => Bank::orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('banks.create', ['bank' => new Bank()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Bank::create($this->validatedData($request));

        return redirect()
            ->route('banks.index')
            ->with('status', 'Banco cadastrado com sucesso.');
    }

    public function edit(Bank $bank): View
    {
        return view('banks.edit', compact('bank'));
    }

    public function update(Request $request, Bank $bank): RedirectResponse
    {
        $bank->update($this->validatedData($request, $bank));

        return redirect()
            ->route('banks.index')
            ->with('status', 'Banco atualizado com sucesso.');
    }

    public function destroy(Bank $bank): RedirectResponse
    {
        $bank->delete();

        return redirect()
            ->route('banks.index')
            ->with('status', 'Banco excluido com sucesso.');
    }

    private function validatedData(Request $request, ?Bank $bank = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('banks', 'name')->ignore($bank),
            ],
        ]);
    }
}
