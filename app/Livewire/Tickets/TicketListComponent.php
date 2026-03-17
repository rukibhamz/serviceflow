<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketListComponent extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $priorityFilter = '';

    #[Url]
    public string $assigneeFilter = '';

    #[Url]
    public string $teamFilter = '';

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDir = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $query = Ticket::query()
            ->with(['requester', 'assignee', 'team'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('subject', 'like', '%'.$this->search.'%')
                  ->orWhere('ulid', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->priorityFilter, fn ($q) => $q->where('priority', $this->priorityFilter))
            ->when($this->assigneeFilter, fn ($q) => $q->where('assignee_id', $this->assigneeFilter))
            ->when($this->teamFilter, fn ($q) => $q->where('team_id', $this->teamFilter))
            ->orderBy($this->sortBy, $this->sortDir);

        $tickets = $query->paginate(20);

        return view('livewire.tickets.ticket-list', compact('tickets'));
    }
}
