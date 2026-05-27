<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CentralCredApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('api.token')->group(function (): void {
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::post('/logout', [AuthApiController::class, 'logout']);

    Route::get('/dashboard', [CentralCredApiController::class, 'dashboard']);
    Route::get('/options', [CentralCredApiController::class, 'options']);

    Route::get('/clients', [CentralCredApiController::class, 'clients']);
    Route::post('/clients', [CentralCredApiController::class, 'storeClient']);
    Route::get('/clients/{client}', [CentralCredApiController::class, 'showClient']);
    Route::put('/clients/{client}', [CentralCredApiController::class, 'updateClient']);
    Route::patch('/clients/{client}', [CentralCredApiController::class, 'updateClient']);
    Route::delete('/clients/{client}', [CentralCredApiController::class, 'destroyClient']);

    Route::get('/contracts', [CentralCredApiController::class, 'contracts']);
    Route::post('/contracts', [CentralCredApiController::class, 'storeContract']);
    Route::get('/contracts/{contract}', [CentralCredApiController::class, 'showContract']);
    Route::put('/contracts/{contract}', [CentralCredApiController::class, 'updateContract']);
    Route::patch('/contracts/{contract}', [CentralCredApiController::class, 'updateContract']);
    Route::delete('/contracts/{contract}', [CentralCredApiController::class, 'destroyContract']);
    Route::post('/contracts/{contract}/contact-history', [CentralCredApiController::class, 'storeContactHistory']);

    Route::get('/banks', [CentralCredApiController::class, 'banks']);
    Route::post('/banks', [CentralCredApiController::class, 'storeBank']);
    Route::put('/banks/{bank}', [CentralCredApiController::class, 'updateBank']);
    Route::patch('/banks/{bank}', [CentralCredApiController::class, 'updateBank']);
    Route::delete('/banks/{bank}', [CentralCredApiController::class, 'destroyBank']);

    Route::get('/agreements', [CentralCredApiController::class, 'agreements']);
    Route::post('/agreements', [CentralCredApiController::class, 'storeAgreement']);
    Route::put('/agreements/{agreement}', [CentralCredApiController::class, 'updateAgreement']);
    Route::patch('/agreements/{agreement}', [CentralCredApiController::class, 'updateAgreement']);
    Route::delete('/agreements/{agreement}', [CentralCredApiController::class, 'destroyAgreement']);

    Route::get('/users', [CentralCredApiController::class, 'users']);
    Route::post('/users', [CentralCredApiController::class, 'storeUser']);
    Route::put('/users/{user}', [CentralCredApiController::class, 'updateUser']);
    Route::patch('/users/{user}', [CentralCredApiController::class, 'updateUser']);
    Route::delete('/users/{user}', [CentralCredApiController::class, 'destroyUser']);

    Route::get('/refinancing', [CentralCredApiController::class, 'refinancingQueue']);
    Route::get('/refinancing-notifications', [CentralCredApiController::class, 'notifications']);
    Route::post('/contracts/{contract}/refinancing-notification/viewed', [CentralCredApiController::class, 'markNotificationViewed']);
    Route::post('/contracts/{contract}/refinancing-notification/not-refinanced', [CentralCredApiController::class, 'markNotificationNotRefinanced']);
});
