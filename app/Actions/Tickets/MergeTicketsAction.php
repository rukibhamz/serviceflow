<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Services\Tickets\TicketStatusMachine;

class MergeTicketsAction
{
    public function __construct(private TicketStatusMachine $statusMachine) {}

    public function execute(Ticket $primary, Ticket $secondary): Ticket
    {
        // Move all comments from secondary to primary
        TicketComment::where('ticket_id', $secondary->id)
            ->update(['ticket_id' => $primary->id]);

        // Move all media from secondary to primary
        foreach ($secondary->getMedia() as $media) {
            $media->copy($primary, $media->collection_name);
        }

        // Mark secondary as merged
        $secondary->merged_into_id = $primary->id;
        $secondary->save();

        // Close secondary ticket via status machine
        // Ensure secondary is in a state that can transition to closed
        if (! $this->statusMachine->canTransition($secondary->status, 'closed')) {
            $secondary->status = 'resolved';
            $secondary->save();
        }
        $this->statusMachine->transition($secondary, 'closed');

        // Add system comment to primary
        $primary->comments()->create([
            'body'      => "Ticket #{$secondary->ulid} was merged into this ticket.",
            'is_system' => true,
        ]);

        return $primary;
    }
}
