<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Services\Tickets\TicketStatusMachine;
use Livewire\Component;
use Livewire\WithPagination;

class TicketTriageQueue extends Component
{
    use WithPagination;

    public array $selected = [];
    public string $bulkAssigneeId = '';
    public string $bulkStatus = '';

    public function toggleSelect(int $ticketId): void
    {
        if (in_array($ticketId, $this->selected, true)) {
            $this->selected = array_values(array_filter($this->selected, fn ($id) => $id !== $ticketId));
        } else {
            $this->selected[] = $ticketId;
        }
    }

    public function selectAll(): void
    {
        $ids = Ticket::query()
            ->whereNull('assignee_id')
            ->where('status', 'open')
            ->paginate(25)
            ->pluck('id')
            ->toArray();

        $this->selected = array_values(array_unique(array_merge($this->selected, $ids)));
    }

    public function bulkAssign(): void
    {
        if (empty($this->selected) || ! $this->bulkAssigneeId) {
            return;
        }

        Ticket::whereIn('id', $this->selected)->update(['assignee_id' => $this->bulkAssigneeId]);

        $this->selected = [];
        $this->bulkAssigneeId = '';
        session()->flash('success', 'Tickets assigned.');
    }

    public function bulkUpdateStatus(): void
    {
        if (empty($this->selected) || ! $this->bulkStatus) {
            return;
        }

        $machine = app(TicketStatusMachine::class);
        $tickets = Ticket::whereIn('id', $this->selected)->get();

        foreach ($tickets as $ticket) {
            try {
                $machine->transition($ticket, $this->bulkStatus);
            } catch (\Throwable) {
                // Skip tickets that cannot transition
            }
        }

        $this->selected = [];
        $this->bulkStatus = '';
        session()->flash('success', 'Statuses updated.');
    }

    public function render()
    {
        $tickets = Ticket::query()
            ->with(['requester', 'team'])
            ->whereNull('assignee_id')
            ->where('status', 'open')
            ->orderBy('created_at', 'asc')
            ->paginate(25);

        $agents = \App\Models\User::orderBy('name')->get(['id', 'name']);
        $statuses = (new TicketStatusMachine)->validStatuses();

        return view('livewire.tickets.triage-queue', compact('tickets', 'agents', 'statuses'));
    }
}
