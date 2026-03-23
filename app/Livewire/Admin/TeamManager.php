<?php

namespace App\Livewire\Admin;

use App\Models\Team;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class TeamManager extends Component
{
    use WithPagination;

    public string $name = '';
    public string $description = '';
    public int $editingTeamId = 0;
    public bool $isCreating = false;

    // Membership management
    public int $selectedTeamId = 0;
    public array $selectedAgents = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
    ];

    public function createTeam(): void
    {
        $this->validate();

        Team::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->reset(['name', 'description', 'isCreating']);
        session()->flash('success', 'Team created successfully.');
    }

    public function editTeam(int $id): void
    {
        $team = Team::findOrFail($id);
        $this->editingTeamId = $id;
        $this->name = $team->name;
        $this->description = $team->description;
        $this->isCreating = true;
    }

    public function updateTeam(): void
    {
        $this->validate();

        $team = Team::findOrFail($this->editingTeamId);
        $team->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->reset(['name', 'description', 'editingTeamId', 'isCreating']);
        session()->flash('success', 'Team updated successfully.');
    }

    public function deleteTeam(int $id): void
    {
        Team::findOrFail($id)->delete();
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
            'teams' => Team::withCount('members')->paginate(10),
            'allAgents' => User::role(['agent', 'manager', 'admin'])->orderBy('name')->get(),
        ])->layout('layouts.agent');
    }
}
