<?php

use App\Http\Controllers\InstallerController;
use App\Http\Controllers\PortalController;
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
    Route::get('/knowledge/{article:slug}/edit', fn (\App\Models\KnowledgeArticle $article) => view('agent.knowledge.edit', compact('article')))->name('knowledge.edit');
    Route::get('/knowledge/{article:slug}', fn (\App\Models\KnowledgeArticle $article) => view('agent.knowledge.show', compact('article')))->name('knowledge.show');
    Route::post('/knowledge/{article:slug}/vote', function (\App\Models\KnowledgeArticle $article, \Illuminate\Http\Request $request) {
        app(\App\Services\Knowledge\ArticleService::class)->vote($article, (bool) $request->input('helpful'));

        return back();
    })->name('knowledge.vote');
});

// Self-service portal
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/kb/search', [PortalController::class, 'searchKb'])->name('kb.search');

    Route::middleware(['auth'])->group(function () {
        Route::get('/', [PortalController::class, 'index'])->name('index');
        Route::get('/tickets', [PortalController::class, 'tickets'])->name('tickets.index');
        Route::get('/tickets/create', [PortalController::class, 'createTicket'])->name('tickets.create');
        Route::post('/tickets', [PortalController::class, 'storeTicket'])->name('tickets.store');
        Route::get('/catalogue', [PortalController::class, 'catalogue'])->name('catalogue.index');
        Route::get('/catalogue/{id}', [PortalController::class, 'catalogueShow'])->name('catalogue.show');
        Route::post('/catalogue/{id}', [PortalController::class, 'catalogueSubmit'])->name('catalogue.submit');
    });

    // Ticket show: auth OR guest token
    Route::get('/tickets/{ticket:ulid}', [PortalController::class, 'showTicket'])->name('tickets.show');
});

// CSAT survey response (public — token-authenticated)
Route::prefix('portal/csat')->name('portal.csat.')->group(function () {
    Route::get('/{token}/rate/{rating}', [PortalController::class, 'csatRate'])->name('rate');
    Route::get('/{token}/feedback', [PortalController::class, 'csatFeedback'])->name('feedback');
    Route::post('/{token}/feedback', [PortalController::class, 'csatStoreFeedback'])->name('feedback.store');
});

Route::prefix('install')->middleware(\App\Http\Middleware\InstallerMiddleware::class)->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('installer.index');
    Route::get('/database', [InstallerController::class, 'database'])->name('installer.database');
    Route::post('/database', [InstallerController::class, 'storeDatabase'])->name('installer.database.store');
    Route::get('/account', [InstallerController::class, 'account'])->name('installer.account');
    Route::post('/account', [InstallerController::class, 'storeAccount'])->name('installer.account.store');
    Route::get('/finish', [InstallerController::class, 'finish'])->name('installer.finish');
});
