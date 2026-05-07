<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\Team;
use App\Models\User;
use App\Services\Change\ChangeApprovalWorkflow;
use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
    public array $change_approver_ids = [];
    public $selected_change_approver_id = null;
    public string $approver_search = '';
    public bool $submit_for_approval = true;
    public string $routePrefix = 'agent';

    protected $rules = [
        'subject' => 'required|min:5|max:255',
        'description' => 'required|min:10',
        'priority' => 'required|in:low,medium,high,urgent',
        'type' => 'required|in:incident,service_request,problem,change',
        'requester_id' => 'required|exists:users,id',
        'team_id' => 'nullable|exists:teams,id',
        'change_approver_ids' => 'nullable|array',
        'change_approver_ids.*' => 'integer|exists:users,id',
        'selected_change_approver_id' => 'nullable|integer|exists:users,id',
        'approver_search' => 'nullable|string|max:255',
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
        if ($this->type === 'change' && $this->submit_for_approval && empty($this->change_approver_ids)) {
            $this->addError('change_approver_ids', 'Select at least one approver to request change approval.');
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
            'cab_approval_required' => $this->type === 'change' && ! empty($this->change_approver_ids),
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

        $this->createChangeApprovers($ticket);


        session()->flash('message', 'Ticket created successfully.');

        return redirect()->route($this->routePrefix . '.tickets.show', $ticket);
    }

    public function render()
    {
        $users = User::orderBy('name')->get();
        $approverUsers = User::query()
            ->whereIn('role', ['manager', 'team_lead'])
            ->when($this->approver_search !== '', function ($q) {
                $term = trim($this->approver_search);
                $q->where(function ($nested) use ($term) {
                    $nested->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $teams = Team::orderBy('name')->get(['id', 'name']);
        $layout = request()->is('admin/*') ? 'layouts.admin' : 'layouts.agent';
        return view('livewire.tickets.create-ticket', [
            'users' => $users,
            'approverUsers' => $approverUsers,
            'teams' => $teams,
        ])->layout($layout);
    }

    public function addApprover(): void
    {
        if (! $this->selected_change_approver_id) {
            return;
        }

        $id = (int) $this->selected_change_approver_id;
        if (! in_array($id, $this->change_approver_ids, true)) {
            $this->change_approver_ids[] = $id;
        }
        $this->selected_change_approver_id = null;
    }

    public function removeApprover(int $userId): void
    {
        $this->change_approver_ids = array_values(array_filter(
            $this->change_approver_ids,
            fn ($id) => (int) $id !== $userId
        ));
    }

    private function createChangeApprovers(Ticket $ticket): void
    {
        if ($this->type !== 'change' || empty($this->change_approver_ids)) {
            return;
        }

        $brandName = app(SettingService::class)->get('brand_name', config('app.name', 'ServiceFlow'));
        $approverIds = collect($this->change_approver_ids)->map(fn ($id) => (int) $id)->unique()->values();

        $eligibleApprovers = User::query()
            ->whereIn('id', $approverIds)
            ->get()
            ->filter(fn (User $user) => $user->hasRole('manager') || $user->role === 'manager' || $user->hasRole('team_lead') || $user->role === 'team_lead')
            ->values();

        foreach ($eligibleApprovers as $user) {
            $approver = $ticket->changeApprovers()->create([
                'user_id' => $user->id,
                'token' => Str::random(40),
            ]);
            $approver->load('user', 'ticket.requester');
            Mail::to($approver->user->email)->send(new \App\Mail\ChangeApprovalRequestMail($approver, $brandName));
        }

        if ($this->submit_for_approval) {
            app(ChangeApprovalWorkflow::class)->submitForApproval($ticket);
        }
    }
}
