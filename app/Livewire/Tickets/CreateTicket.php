<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateTicket extends Component
{
    public $subject = '';
    public $description = '';
    public $priority = 'medium';
    public $type = 'incident';
    public $requester_id = null;

    protected $rules = [
        'subject' => 'required|min:5|max:255',
        'description' => 'required|min:10',
        'priority' => 'required|in:low,medium,high,urgent',
        'type' => 'required|in:incident,service_request,problem,change',
        'requester_id' => 'required|exists:users,id',
    ];

    public function mount()
    {
        $this->requester_id = Auth::id();
    }

    public function save()
    {
        $this->validate();

        $ticket = Ticket::create([
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'type' => $this->type,
            'requester_id' => $this->requester_id,
            'status' => 'open',
            'source' => 'web',
        ]);

        app(\App\Services\Sla\SlaService::class)->assignPolicy($ticket);

        session()->flash('message', 'Ticket created successfully.');

        return redirect()->route('agent.tickets.show', $ticket);
    }

    public function render()
    {
        $users = User::orderBy('name')->get();
        return view('livewire.tickets.create-ticket', [
            'users' => $users
        ])->layout('layouts.agent');
    }
}
