<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactHistoryController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('clients', ClientController::class)->except(['edit', 'update', 'destroy']);
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])
        ->middleware('permission:editar_cliente')
        ->name('clients.edit');
    Route::match(['put', 'patch'], '/clients/{client}', [ClientController::class, 'update'])
        ->middleware('permission:editar_cliente')
        ->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])
        ->middleware('permission:excluir_cliente')
        ->name('clients.destroy');

    Route::resource('contracts', ContractController::class)->except(['edit', 'update', 'destroy']);
    Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])
        ->middleware('permission:editar_contrato')
        ->name('contracts.edit');
    Route::match(['put', 'patch'], '/contracts/{contract}', [ContractController::class, 'update'])
        ->middleware('permission:editar_contrato')
        ->name('contracts.update');
    Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])
        ->middleware('permission:excluir_contrato')
        ->name('contracts.destroy');

    Route::get('/banks', [BankController::class, 'index'])
        ->middleware('permission:visualizar_bancos')
        ->name('banks.index');
    Route::get('/banks/create', [BankController::class, 'create'])
        ->middleware('permission:criar_bancos')
        ->name('banks.create');
    Route::post('/banks', [BankController::class, 'store'])
        ->middleware('permission:criar_bancos')
        ->name('banks.store');
    Route::get('/banks/{bank}/edit', [BankController::class, 'edit'])
        ->middleware('permission:editar_bancos')
        ->name('banks.edit');
    Route::match(['put', 'patch'], '/banks/{bank}', [BankController::class, 'update'])
        ->middleware('permission:editar_bancos')
        ->name('banks.update');
    Route::delete('/banks/{bank}', [BankController::class, 'destroy'])
        ->middleware('permission:excluir_bancos')
        ->name('banks.destroy');

    Route::get('/agreements', [AgreementController::class, 'index'])
        ->middleware('permission:visualizar_convenios')
        ->name('agreements.index');
    Route::get('/agreements/create', [AgreementController::class, 'create'])
        ->middleware('permission:criar_convenio')
        ->name('agreements.create');
    Route::post('/agreements', [AgreementController::class, 'store'])
        ->middleware('permission:criar_convenio')
        ->name('agreements.store');
    Route::get('/agreements/{agreement}/edit', [AgreementController::class, 'edit'])
        ->middleware('permission:editar_convenio')
        ->name('agreements.edit');
    Route::match(['put', 'patch'], '/agreements/{agreement}', [AgreementController::class, 'update'])
        ->middleware('permission:editar_convenio')
        ->name('agreements.update');
    Route::delete('/agreements/{agreement}', [AgreementController::class, 'destroy'])
        ->middleware('permission:excluir_convenio')
        ->name('agreements.destroy');

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:visualizar_usuarios')
        ->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('permission:criar_usuarios')
        ->name('users.create');
    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:criar_usuarios')
        ->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:editar_usuarios')
        ->name('users.edit');
    Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:editar_usuarios')
        ->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:excluir_usuarios')
        ->name('users.destroy');

    Route::get('/refinancing', [ContractController::class, 'refinancing'])
        ->name('contracts.refinancing');

    Route::post('/contracts/{contract}/contact-history', [ContactHistoryController::class, 'store'])
        ->name('contracts.contact-history.store');
});
