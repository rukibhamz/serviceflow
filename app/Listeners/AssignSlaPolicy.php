<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Services\Sla\SlaService;

class AssignSlaPolicy
{
    public function __construct(private SlaService $slaService) {}

    public function handle(TicketCreated $event): void
    {
        $this->slaService->assignPolicy($event->ticket);
    }
}
