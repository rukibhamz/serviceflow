<?php

namespace App\Livewire\Tickets;

use App\Actions\Tickets\MergeTicketsAction;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Services\Tickets\TicketStatusMachine;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TicketResource extends Component
{
    public Ticket $ticket;

    public string $commentBody = '';
    public bool $isInternal = false;
    public string $newStatus = '';
    public string $newPriority = '';
    public string $newAssigneeId = '';
    public string $mergeTargetUlid = '';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
        $this->newAssigneeId = (string) ($ticket->assignee_id ?? '');
    }

    public function addComment(): void
    {
        $this->validate(['commentBody' => 'required|string|min:1']);

        $this->ticket->comments()->create([
            'user_id'     => Auth::id(),
            'body'        => $this->commentBody,
            'is_internal' => $this->isInternal,
        ]);

        $this->reset('commentBody', 'isInternal');
        $this->ticket->refresh();
        session()->flash('success', 'Comment added.');
    }

    public function updateStatus(): void
    {
        if ($this->newStatus === $this->ticket->status) {
            return;
        }

        try {
            app(TicketStatusMachine::class)->transition($this->ticket, $this->newStatus);
            $this->ticket->refresh();
            session()->flash('success', 'Status updated.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
            $this->newStatus = $this->ticket->status;
        }
    }

    public function updatePriority(): void
    {
        $this->ticket->update(['priority' => $this->newPriority]);
        $this->ticket->refresh();
        session()->flash('success', 'Priority updated.');
    }

    public function updateAssignee(): void
    {
        $this->ticket->update([
            'assignee_id' => $this->newAssigneeId ?: null,
        ]);
        $this->ticket->refresh();
        session()->flash('success', 'Assignee updated.');
    }

    public function mergeInto(): void
    {
        $this->validate(['mergeTargetUlid' => 'required|string']);

        $target = Ticket::where('ulid', $this->mergeTargetUlid)->first();

        if (! $target) {
            session()->flash('error', 'Target ticket not found.');
            return;
        }

        if ($target->id === $this->ticket->id) {
            session()->flash('error', 'Cannot merge a ticket into itself.');
            return;
        }

        app(MergeTicketsAction::class)->execute($target, $this->ticket);

        $this->redirect(route('agent.tickets.show', $target->ulid));
    }

    public function render()
    {
        $this->ticket->load(['requester', 'assignee', 'team', 'slaTimers', 'comments.author']);

        $agents = \App\Models\User::orderBy('name')->get(['id', 'name']);
        $statuses = (new TicketStatusMachine)->validStatuses();

        return view('livewire.tickets.ticket-resource', compact('agents', 'statuses'));
    }
}
