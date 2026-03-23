<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketWatcher;
use Illuminate\Support\Facades\Notification;

class TicketSubscriptionService
{
    public function subscribe(Ticket $ticket, int $userId): void
    {
        TicketWatcher::firstOrCreate([
            'ticket_id' => $ticket->id,
            'user_id' => $userId,
        ]);
    }

    public function unsubscribe(Ticket $ticket, int $userId): void
    {
        TicketWatcher::where('ticket_id', $ticket->id)
            ->where('user_id', $userId)
            ->delete();
    }

    public function notifyWatchers(Ticket $ticket, string $message, ?int $excludeUserId = null): void
    {
        $watchers = $ticket->watchers()
            ->with('user')
            ->get()
            ->pluck('user');

        if ($excludeUserId) {
            $watchers = $watchers->filter(fn ($u) => $u->id !== $excludeUserId);
        }

        // Potential: Dispatch Email/In-app notifications here
        // Notification::send($watchers, new TicketUpdatedNotification($ticket, $message));
    }
}
