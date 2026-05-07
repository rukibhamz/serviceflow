<?php

namespace App\Livewire\Admin;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class TeamManager extends Component
{
    use WithPagination;

    public string $name = '';
    public string $description = '';
    public string $inboundEmail = '';
    public bool $inboundEmailEnabled = false;
    public int $editingTeamId = 0;
    public bool $isCreating = false;

    // Membership management
    public int $selectedTeamId = 0;
    public array $selectedAgents = [];
    public ?int $selectedTeamLeadId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'inboundEmail' => 'nullable|email|max:255',
        'inboundEmailEnabled' => 'boolean',
    ];

    public function mount(): void
    {
        if (request()->boolean('new')) {
            $this->isCreating = true;
            return;
        }

        $editId = (int) request()->query('edit', 0);
        if ($editId > 0) {
            $team = Team::find($editId);
            if ($team) {
                $this->editingTeamId = $team->id;
                $this->name = $team->name;
                $this->description = (string) ($team->description ?? '');
                $this->inboundEmail = (string) ($team->inbound_email ?? '');
                $this->inboundEmailEnabled = (bool) ($team->inbound_email_enabled ?? false);
                $this->isCreating = true;
            }
            return;
        }

        $membersId = (int) request()->query('members', 0);
        if ($membersId > 0) {
            $team = Team::find($membersId);
            if ($team) {
                $this->selectedTeamId = $team->id;
                $this->selectedAgents = $team->members()->pluck('users.id')->toArray();
                $this->selectedTeamLeadId = $team->team_lead_id;
            }
        }
    }

    public function createTeam(): void
    {
        $this->validate();

        try {
            Team::create([
                'tenant_id' => auth()->user()?->tenant_id,
                'name' => $this->name,
                'description' => $this->description,
                'inbound_email' => $this->inboundEmail ?: null,
                'inbound_email_enabled' => $this->inboundEmailEnabled,
            ]);

            $this->reset(['name', 'description', 'inboundEmail', 'inboundEmailEnabled', 'isCreating']);
            $this->resetPage();
            session()->flash('success', 'Team created successfully.');
        } catch (\Throwable $e) {
            Log::error('Team creation failed.', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $this->addError('general', 'Unable to create team right now. Please try again.');
        }
    }

    public function startCreate(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'inboundEmail', 'inboundEmailEnabled', 'editingTeamId']);
        $this->isCreating = true;
    }

    public function editTeam(int $id): void
    {
        $team = Team::findOrFail($id);
        $this->editingTeamId = $id;
        $this->name = $team->name;
        $this->description = $team->description;
        $this->inboundEmail = (string) ($team->inbound_email ?? '');
        $this->inboundEmailEnabled = (bool) ($team->inbound_email_enabled ?? false);
        $this->isCreating = true;
    }

    public function updateTeam(): void
    {
        $this->validate();

        $team = Team::findOrFail($this->editingTeamId);
        $team->update([
            'name' => $this->name,
            'description' => $this->description,
            'inbound_email' => $this->inboundEmail ?: null,
            'inbound_email_enabled' => $this->inboundEmailEnabled,
        ]);

        $this->reset(['name', 'description', 'inboundEmail', 'inboundEmailEnabled', 'editingTeamId', 'isCreating']);
        $this->resetPage();
        session()->flash('success', 'Team updated successfully.');
    }

    public function saveTeam(): void
    {
        if ($this->editingTeamId > 0) {
            $this->updateTeam();
            return;
        }

        $this->createTeam();
    }

    public function deleteTeam(int $id): void
    {
        Team::findOrFail($id)->delete();
        $this->resetPage();
        session()->flash('success', 'Team deleted successfully.');
    }

    public function manageMembers(int $teamId): void
    {
        $this->selectedTeamId = $teamId;
        $team = Team::findOrFail($teamId);
        $this->selectedAgents = $team->members()->pluck('users.id')->toArray();
    }

    public function saveMembers(): void
    {
        $team = Team::findOrFail($this->selectedTeamId);
        $team->members()->sync($this->selectedAgents);

        $this->reset(['selectedTeamId', 'selectedAgents']);
        session()->flash('success', 'Team members updated.');
    }

    public function render()
    {
        return view('livewire.admin.team-manager', [
            'teams'     => Team::with('lead')->withCount('members')->latest('id')->paginate(10),
            'allAgents' => User::whereIn('role', ['agent', 'admin', 'team_lead'])
                ->orWhereHas('roles', fn($q) => $q->whereIn('name', ['agent', 'admin', 'team_lead']))
                ->orderBy('name')
                ->get(),
            'teamLeadUsers' => User::whereIn('role', ['team_lead', 'admin'])
                ->orWhereHas('roles', fn($q) => $q->whereIn('name', ['team_lead', 'admin']))
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ])->layout('layouts.admin');
    }
}
