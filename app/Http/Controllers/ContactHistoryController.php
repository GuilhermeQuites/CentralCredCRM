<?php

namespace App\Http\Controllers;

use App\Models\ContactHistory;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactHistoryController extends Controller
{
    public function store(Request $request, Contract $contract): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(ContactHistory::TYPES)],
            'description' => ['nullable', 'string'],
            'contacted_at' => ['required', 'date'],
        ]);

        $contract->contactHistories()->create($data);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('status', 'Contato registrado com sucesso.');
    }
}
