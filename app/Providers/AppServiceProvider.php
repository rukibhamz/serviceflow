<?php

namespace App\Providers;

use App\Events\CommentAdded;
use App\Events\SlaBreached;
use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use App\Listeners\RunAutomationEngine;
use App\Models\KnowledgeArticle;
use App\Models\Ticket;
use App\Observers\KnowledgeArticleObserver;
use App\Observers\TicketObserver;
use App\View\Composers\SettingsComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Ticket::observe(TicketObserver::class);
        KnowledgeArticle::observe(KnowledgeArticleObserver::class);

        // Apply branding settings to all views
        View::composer('*', SettingsComposer::class);

        // Wire automation engine into all registered trigger events
        foreach ([TicketCreated::class, TicketUpdated::class, CommentAdded::class, SlaBreached::class] as $eventClass) {
            Event::listen($eventClass, RunAutomationEngine::class);
        }

        // Fix for 405/404 errors in subdirectory: ensure Livewire hits the correct routes
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle);
        });

        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire.js', $handle);
        });
    }
}
