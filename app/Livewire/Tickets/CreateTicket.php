<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateTicket extends Component
{
    use WithFileUploads;

    public $subject = '';
    public $description = '';
    public $priority = 'medium';
    public $type = 'incident';
    public $requester_id = null;
    public $team_id = null;
    public $attachments = [];
    public string $routePrefix = 'agent';

    protected $rules = [
        'subject' => 'required|min:5|max:255',
        'description' => 'required|min:10',
        'priority' => 'required|in:low,medium,high,urgent',
        'type' => 'required|in:incident,service_request,problem,change',
        'requester_id' => 'required|exists:users,id',
        'team_id' => 'nullable|exists:teams,id',
        'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx|max:10240',
    ];

    public function mount()
    {
        $this->requester_id = Auth::id();
        $this->routePrefix = request()->is('admin/*') ? 'admin' : 'agent';
        // Allow pre-selecting type via query string e.g. ?type=problem
        $requestedType = request()->query('type');
        if ($requestedType && in_array($requestedType, ['incident', 'service_request', 'problem', 'change'])) {
            $this->type = $requestedType;
        }
    }

    public function save()
    {
        $this->validate();
        if (in_array($this->type, ['problem', 'change'], true) && ! $this->team_id) {
            $this->addError('team_id', 'Team is required for problems and change requests.');
            return;
        }

        $ticket = Ticket::create([
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'type' => $this->type,
            'requester_id' => $this->requester_id ?: Auth::id(),
            'team_id' => $this->team_id ?: null,
            'status' => 'open',
            'source' => 'web',
        ]);

        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $ticket->addMedia($file->getRealPath())
                    ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    ->usingFileName($file->hashName())
                    ->toMediaCollection('attachments');
            }
        }

        app(\App\Services\Sla\SlaService::class)->assignPolicy($ticket);
        app(\App\Services\Tickets\TicketAssignmentService::class)->autoAssign($ticket);


        session()->flash('message', 'Ticket created successfully.');

        return redirect()->route($this->routePrefix . '.tickets.show', $ticket);
    }

    public function render()
    {
        $users = User::orderBy('name')->get();
        $teams = Team::orderBy('name')->get(['id', 'name']);
        $layout = request()->is('admin/*') ? 'layouts.admin' : 'layouts.agent';
        return view('livewire.tickets.create-ticket', [
            'users' => $users,
            'teams' => $teams,
        ])->layout($layout);
    }
}
