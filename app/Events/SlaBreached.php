<?php

namespace App\Events;

use App\Models\SlaTimer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlaBreached implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly SlaTimer $timer) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('tickets'),
            new Channel("ticket.{$this->timer->ticket->ulid}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'timer_id'    => $this->timer->id,
            'ticket_ulid' => $this->timer->ticket->ulid,
            'type'        => $this->timer->type,
        ];
    }
}
