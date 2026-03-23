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
        
        $ticketsByStatus = [];
        $allTickets = Ticket::with(['requester', 'assignee'])
            ->whereIn('status', $statuses)
            ->where(function($query) {
                $query->where('status', '!=', 'closed')
                      ->orWhere('closed_at', '>=', now()->subDays(3)); // Show only recently closed items
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        foreach ($statuses as $status) {
            $ticketsByStatus[$status] = $allTickets->where('status', $status);
        }

        return view('livewire.tickets.ticket-kanban', [
            'statuses' => $statuses,
            'ticketsByStatus' => $ticketsByStatus
        ])->layout('layouts.agent');
    }
}
