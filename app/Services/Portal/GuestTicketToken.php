<?php

namespace App\Services\Portal;

use App\Models\Ticket;
use Illuminate\Support\Facades\URL;

class GuestTicketToken
{
    /**
     * Generate a signed URL that allows unauthenticated access to a ticket's status page.
     * The URL expires after the given number of minutes (default 7 days).
     */
    public function generate(Ticket $ticket, int $expiresInMinutes = 10080): string
    {
        return URL::temporarySignedRoute(
            'portal.tickets.show',
            now()->addMinutes($expiresInMinutes),
            ['ticket' => $ticket->ulid],
        );
    }

    /**
     * Validate that the current request carries a valid signature for the given ticket.
     */
    public function validate(\Illuminate\Http\Request $request, Ticket $ticket): bool
    {
        if (! $request->hasValidSignature()) {
            return false;
        }

        // Ensure the signed URL is for this specific ticket
        $routeTicket = $request->route('ticket');

        return $routeTicket instanceof Ticket
            ? $routeTicket->id === $ticket->id
            : $routeTicket === $ticket->ulid;
    }
}
