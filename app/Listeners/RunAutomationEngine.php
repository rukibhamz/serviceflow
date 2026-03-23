<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use App\Events\SlaBreached;
use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use App\Models\Ticket;
use App\Services\Automation\AutomationEngine;
use App\Services\Automation\TriggerRegistry;

/**
 * Universal listener that routes any registered trigger event
 * through the AutomationEngine.
 */
class RunAutomationEngine
{
    public function __construct(
        private readonly AutomationEngine $engine,
        private readonly TriggerRegistry $registry,
    ) {}

    public function handle(object $event): void
    {
        $ticket = $this->resolveTicket($event);

        if ($ticket === null) {
            return;
        }

        $triggerName = $this->registry->triggerForEvent($event::class);

        if ($triggerName === null) {
            return;
        }

        $this->engine->process($triggerName, $ticket);
    }

    private function resolveTicket(object $event): ?Ticket
    {
        return match (true) {
            $event instanceof TicketCreated => $event->ticket,
            $event instanceof TicketUpdated => $event->ticket,
            $event instanceof SlaBreached   => $event->ticket,
            $event instanceof CommentAdded  => $event->comment->ticket ?? null,
            default                         => null,
        };
    }
}
