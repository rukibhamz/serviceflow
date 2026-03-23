<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Public channel — all authenticated agents can subscribe
Broadcast::channel('tickets', function ($user) {
    return $user !== null;
});

// Per-ticket channel — requester or any agent can subscribe
Broadcast::channel('ticket.{ulid}', function ($user, string $ulid) {
    if ($user === null) {
        return false;
    }

    $ticket = \App\Models\Ticket::where('ulid', $ulid)->first();

    if (! $ticket) {
        return false;
    }

    // Requester or any agent/admin can listen
    return $ticket->requester_id === $user->id
        || $user->hasAnyRole(['admin', 'manager', 'agent']);
});
