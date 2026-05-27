<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('contracts:sync-paid-installments', function (\App\Services\PaidInstallmentSyncService $service) {
    $updated = $service->sync();

    $this->info("Parcelas pagas sincronizadas. Contratos atualizados: {$updated}");
})->purpose('Sync contract paid installments from first discount date');

Schedule::command('contracts:sync-paid-installments')
    ->dailyAt('01:00')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping();
