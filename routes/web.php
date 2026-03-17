<?php

use App\Http\Controllers\InstallerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/tickets', fn () => view('agent.tickets.index'))->name('tickets.index');
    Route::get('/tickets/{ticket:ulid}', fn (\App\Models\Ticket $ticket) => view('agent.tickets.show', compact('ticket')))->name('tickets.show');
    Route::get('/triage', fn () => view('agent.tickets.triage'))->name('tickets.triage');
});

Route::prefix('install')->middleware(\App\Http\Middleware\InstallerMiddleware::class)->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('installer.index');
    Route::get('/database', [InstallerController::class, 'database'])->name('installer.database');
    Route::post('/database', [InstallerController::class, 'storeDatabase'])->name('installer.database.store');
    Route::get('/account', [InstallerController::class, 'account'])->name('installer.account');
    Route::post('/account', [InstallerController::class, 'storeAccount'])->name('installer.account.store');
    Route::get('/finish', [InstallerController::class, 'finish'])->name('installer.finish');
});
