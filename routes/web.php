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

    Route::get('/knowledge', fn () => view('agent.knowledge.index'))->name('knowledge.index');
    Route::get('/knowledge/create', fn () => view('agent.knowledge.create'))->name('knowledge.create');
    Route::get('/knowledge/{article:slug}', fn (\App\Models\KnowledgeArticle $article) => view('agent.knowledge.show', compact('article')))->name('knowledge.show');
    Route::post('/knowledge/{article:slug}/vote', function (\App\Models\KnowledgeArticle $article, \Illuminate\Http\Request $request) {
        app(\App\Services\Knowledge\ArticleService::class)->vote($article, (bool) $request->input('helpful'));

        return back();
    })->name('knowledge.vote');
});

Route::prefix('install')->middleware(\App\Http\Middleware\InstallerMiddleware::class)->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('installer.index');
    Route::get('/database', [InstallerController::class, 'database'])->name('installer.database');
    Route::post('/database', [InstallerController::class, 'storeDatabase'])->name('installer.database.store');
    Route::get('/account', [InstallerController::class, 'account'])->name('installer.account');
    Route::post('/account', [InstallerController::class, 'storeAccount'])->name('installer.account.store');
    Route::get('/finish', [InstallerController::class, 'finish'])->name('installer.finish');
});
