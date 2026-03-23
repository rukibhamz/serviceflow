<?php

namespace App\Events;

use App\Models\TicketComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly TicketComment $comment) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("ticket.{$this->comment->ticket->ulid}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->comment->id,
            'ticket_ulid' => $this->comment->ticket->ulid,
            'author'      => $this->comment->author?->name ?? 'System',
            'body'        => $this->comment->is_internal ? null : $this->comment->body,
            'is_internal' => $this->comment->is_internal,
            'created_at'  => $this->comment->created_at->toISOString(),
        ];
    }
}
