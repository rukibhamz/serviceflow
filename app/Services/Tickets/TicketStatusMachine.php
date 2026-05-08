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
        $from = trim(strtolower($from));
        $to = trim(strtolower($to));
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function transition(Ticket $ticket, string $to): void
    {
        if (! $this->canTransition($ticket->status, $to)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition ticket from '{$ticket->status}' to '{$to}'."
            );
        }

        $from = $ticket->status;
        $ticket->status = $to;

        // SLA Pause/Resume logic
        if ($to === 'pending' && $from !== 'pending') {
            $ticket->slaTimers()->whereNull('stopped_at')->where('breached', false)->get()
                ->each(function ($timer) {
                    $timer->pauses()->create([
                        'paused_at' => now(),
                        'reason' => 'Ticket moved to pending'
                    ]);
                });
        } elseif ($from === 'pending' && $to !== 'pending') {
            $ticket->slaTimers()->whereNull('stopped_at')->where('breached', false)->get()
                ->each(function ($timer) {
                    $activePause = $timer->pauses()->whereNull('resumed_at')->latest('paused_at')->first();
                    if ($activePause) {
                        $now = now();
                        $activePause->update(['resumed_at' => $now]);
                        $duration = $activePause->paused_at->diffInMinutes($now);
                        
                        $timer->update([
                            'due_at' => \Carbon\Carbon::parse($timer->due_at)->addMinutes($duration)
                        ]);
                    }
                });
        }

        if ($to === 'closed' && $ticket->closed_at === null) {
            $ticket->closed_at = now();
            $ticket->slaTimers()->whereNull('stopped_at')->update(['stopped_at' => now()]);
        }

        $ticket->save();
    }

    public function validStatuses(): array
    {
        return ['open', 'in_progress', 'pending', 'resolved', 'closed'];
    }
}
