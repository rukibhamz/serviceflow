<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Services\Tickets\TicketStatusMachine;
use Livewire\Component;

class TicketKanban extends Component
{
    public function updateTicketStatus($ticketId, $newStatus)
    {
        $ticket = Ticket::find($ticketId);
        
        if ($ticket && $ticket->status !== $newStatus) {
            try {
                $machine = app(TicketStatusMachine::class);
                $machine->transition($ticket, $newStatus);
            } catch (\Throwable $e) {
                $this->addError('status', $e->getMessage());
            }
        }
    }

    public function render()
    {
        $statuses = (new TicketStatusMachine)->validStatuses();

        $allTickets = Ticket::with(['requester', 'assignee'])
            ->where(function ($q) {
                $q->whereNotIn('status', ['closed'])
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'closed')
                         ->where('closed_at', '>=', now()->subDays(3));
                  });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $ticketsByStatus = [];
        foreach ($statuses as $status) {
            $ticketsByStatus[$status] = $allTickets->where('status', $status)->values();
        }

        $layout = request()->is('admin/*') ? 'layouts.admin' : 'layouts.agent';

        return view('livewire.tickets.ticket-kanban', [
            'statuses'        => $statuses,
            'ticketsByStatus' => $ticketsByStatus,
        ])->layout($layout);
    }
}
