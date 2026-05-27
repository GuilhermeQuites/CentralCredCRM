<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\RefinancingNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RefinancingNotificationController extends Controller
{
    public function index(RefinancingNotificationService $service): JsonResponse
    {
        $notifications = $service->activePayload();
        $showLoginAlert = count($notifications) > 0
            && ! session()->has('refinancing_notification_alert_shown');

        if ($showLoginAlert) {
            session(['refinancing_notification_alert_shown' => true]);
        }

        return response()->json([
            'count' => count($notifications),
            'notifications' => $notifications,
            'show_login_alert' => $showLoginAlert,
        ]);
    }

    public function markViewed(Contract $contract, RefinancingNotificationService $service): JsonResponse
    {
        $service->markViewed($contract);

        return $this->index($service);
    }

    public function markNotRefinanced(
        Request $request,
        Contract $contract,
        RefinancingNotificationService $service,
    ): JsonResponse {
        $data = $request->validate([
            'notify_after_paid_installments' => ['required', 'integer', 'min:' . ($contract->paid_installments + 1)],
        ], [
            'notify_after_paid_installments.required' => 'Informe em qual parcela a notificacao deve voltar.',
            'notify_after_paid_installments.min' => 'A proxima notificacao deve ser maior que a quantidade atual de parcelas pagas.',
        ]);

        $service->markNotRefinanced($contract, (int) $data['notify_after_paid_installments']);

        return $this->index($service);
    }

    public function markViewedFromContract(
        Contract $contract,
        RefinancingNotificationService $service,
    ): RedirectResponse {
        $service->markViewed($contract);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('status', 'Notificacao marcada como visualizada.');
    }

    public function markNotRefinancedFromContract(
        Request $request,
        Contract $contract,
        RefinancingNotificationService $service,
    ): RedirectResponse {
        $data = $request->validate([
            'notify_after_paid_installments' => ['required', 'integer', 'min:' . ($contract->paid_installments + 1)],
        ], [
            'notify_after_paid_installments.required' => 'Informe em qual parcela a notificacao deve voltar.',
            'notify_after_paid_installments.min' => 'A proxima notificacao deve ser maior que a quantidade atual de parcelas pagas.',
        ]);

        $service->markNotRefinanced($contract, (int) $data['notify_after_paid_installments']);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('status', 'Contrato marcado como nao refinanciado.');
    }
}
