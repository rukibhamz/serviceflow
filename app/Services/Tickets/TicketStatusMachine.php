<?php

namespace App\Services\Tickets;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Ticket;

class TicketStatusMachine
{
    const TRANSITIONS = [
        'open'        => ['in_progress'],
        'in_progress' => ['pending', 'resolved'],
        'pending'     => ['in_progress', 'resolved'],
        'resolved'    => ['closed', 'open'],
        'closed'      => ['open'],
    ];

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function transition(Ticket $ticket, string $to): void
    {
        if (! $this->canTransition($ticket->status, $to)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition ticket from '{$ticket->status}' to '{$to}'."
            );
        }

        $ticket->status = $to;

        if ($to === 'closed' && $ticket->closed_at === null) {
            $ticket->closed_at = now();
        }

        $ticket->save();
    }

    public function validStatuses(): array
    {
        return ['open', 'in_progress', 'pending', 'resolved', 'closed'];
    }
}
