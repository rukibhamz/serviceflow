<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Ticket $ticket) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('tickets'),
            new Channel("ticket.{$this->ticket->ulid}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'       => $this->ticket->id,
            'ulid'     => $this->ticket->ulid,
            'status'   => $this->ticket->status,
            'priority' => $this->ticket->priority,
        ];
    }
}
