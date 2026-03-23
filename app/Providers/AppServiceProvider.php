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
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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

        // Wire automation engine into all registered trigger events
        foreach ([TicketCreated::class, TicketUpdated::class, CommentAdded::class, SlaBreached::class] as $eventClass) {
            Event::listen($eventClass, RunAutomationEngine::class);
        }
    }
}
