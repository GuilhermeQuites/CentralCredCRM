<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function index(): View
    {
        return view('agreements.index', [
            'agreements' => Agreement::orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('agreements.create', ['agreement' => new Agreement()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Agreement::create($this->validatedData($request));

        return redirect()
            ->route('agreements.index')
            ->with('status', 'Convenio cadastrado com sucesso.');
    }

    public function edit(Agreement $agreement): View
    {
        return view('agreements.edit', compact('agreement'));
    }

    public function update(Request $request, Agreement $agreement): RedirectResponse
    {
        $agreement->update($this->validatedData($request, $agreement));

        return redirect()
            ->route('agreements.index')
            ->with('status', 'Convenio atualizado com sucesso.');
    }

    public function destroy(Agreement $agreement): RedirectResponse
    {
        $agreement->delete();

        return redirect()
            ->route('agreements.index')
            ->with('status', 'Convenio excluido com sucesso.');
    }

    private function validatedData(Request $request, ?Agreement $agreement = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('agreements', 'name')->ignore($agreement),
            ],
        ]);
    }
}
