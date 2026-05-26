<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contract;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $contracts = Contract::query()
            ->with(['client', 'bankRecord'])
            ->where('status', 'active')
            ->latest()
            ->get();

        $contractsWithStatus = $contracts->map(function (Contract $contract) {
            $contract->refinancing = $contract->refinancingStatus();

            return $contract;
        });

        return view('dashboard.index', [
            'clientsCount' => Client::count(),
            'activeContractsCount' => $contracts->count(),
            'eligibleCount' => $contractsWithStatus
                ->where('refinancing.status', 'eligible')
                ->count(),
            'followUpCount' => $contractsWithStatus
                ->where('refinancing.status', 'waiting')
                ->where('refinancing.remaining_installments', '<=', 6)
                ->count(),
            'contracts' => $contractsWithStatus->take(10),
        ]);
    }
}
