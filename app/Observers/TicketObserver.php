<?php

namespace App\Observers;

use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use App\Models\Ticket;
use App\Services\Portal\CsatService;
use Illuminate\Support\Facades\Log;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        TicketCreated::dispatch($ticket);
    }

    public function updated(Ticket $ticket): void
    {
        TicketUpdated::dispatch($ticket);

        if ($ticket->wasChanged('status') && $ticket->status === 'closed' && $ticket->closed_at === null) {
            $ticket->timestamps = false;
            $ticket->closed_at  = now();
            $ticket->save();
            $ticket->timestamps = true;

            // Send CSAT survey when ticket is closed
            app(CsatService::class)->sendSurvey($ticket);
        }
    }

    public function deleted(Ticket $ticket): void
    {
        Log::info("Ticket deleted", ['ticket_id' => $ticket->id, 'ulid' => $ticket->ulid]);
    }
}
