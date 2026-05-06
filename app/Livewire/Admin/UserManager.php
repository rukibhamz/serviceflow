<?php

namespace App\Livewire\Admin;

use App\Mail\UserInvitationMail;
use App\Models\Team;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public string $search = '';

    // Invite form
    public bool   $showInviteForm = false;
    public string $inviteEmail    = '';
    public string $inviteRole     = 'user';

    // Edit user panel
    public ?int   $editingUserId  = null;
    public string $editName       = '';
    public string $editEmail      = '';
    public string $editRole       = 'user';
    public bool   $editIsActive   = true;
    public array  $editTeams      = [];

    protected $rules = [
        'inviteEmail' => 'required|email|unique:users,email|unique:user_invitations,email',
        'inviteRole'  => 'required|in:admin,agent,user',
    ];

    public function mount(): void
    {
        if (request()->boolean('new')) {
            $this->showInviteForm = true;
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }

    // ── Invite ────────────────────────────────────────────────────────────────

    public function sendInvite(): void
    {
        $this->resetErrorBag();

        $this->validate([
            'inviteEmail' => 'required|email|unique:users,email|unique:user_invitations,email',
            'inviteRole'  => 'required|in:admin,agent,user',
        ]);

        try {
            $invitation = UserInvitation::create([
                'email'      => $this->inviteEmail,
                'role'       => $this->inviteRole,
                'token'      => Str::random(40),
                'invited_by' => Auth::id(),
                'expires_at' => now()->addDays(7),
            ]);
        } catch (\Throwable $e) {
            Log::error('User invitation creation failed.', [
                'message' => $e->getMessage(),
                'email' => $this->inviteEmail,
                'role' => $this->inviteRole,
                'user_id' => Auth::id(),
            ]);
            $this->addError('inviteGeneral', 'Unable to create invitation right now. Please check DB/migrations and try again.');
            return;
        }

        $brandName = app(SettingService::class)->get('brand_name', config('app.name', 'ServiceFlow'));

        try {
            Mail::to($invitation->email)->send(new UserInvitationMail($invitation, $brandName));
        } catch (\Throwable $e) {
            session()->flash('success', "Invitation created for {$invitation->email}, but email sending failed. Check mail settings.");
            return;
        }

        $this->inviteEmail    = '';
        $this->inviteRole     = 'user';
        $this->showInviteForm = false;

        session()->flash('success', "Invitation sent to {$invitation->email}.");
    }

    public function startInvite(): void
    {
        $this->resetValidation();
        $this->reset(['inviteEmail']);
        $this->inviteRole = 'user';
        $this->showInviteForm = true;
    }

    public function cancelInvitation(int $id): void
    {
        UserInvitation::findOrFail($id)->delete();
        session()->flash('success', 'Invitation cancelled.');
    }

    // ── Edit user ─────────────────────────────────────────────────────────────

    public function editUser(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingUserId = $id;
        $this->editName      = $user->name;
        $this->editEmail     = $user->email;
        $this->editRole      = $user->role ?? 'user';
        $this->editIsActive  = $user->is_active ?? true;
        $this->editTeams     = $user->teams()->pluck('teams.id')->toArray();
    }

    public function saveUser(): void
    {
        $this->validate([
            'editName'  => 'required|string|max:255',
            'editEmail' => 'required|email|unique:users,email,' . $this->editingUserId,
            'editRole'  => 'required|in:admin,agent,user',
        ]);

        $user = User::findOrFail($this->editingUserId);
        $user->update([
            'name'      => $this->editName,
            'email'     => $this->editEmail,
            'role'      => $this->editRole,
            'is_active' => $this->editIsActive,
        ]);

        // Sync teams
        $user->teams()->sync($this->editTeams);

        // Sync Spatie role
        $user->syncRoles([$this->editRole]);

        $this->editingUserId = null;
        session()->flash('success', 'User updated.');
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();
        session()->flash('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function render()
    {
        $users = User::with('teams')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(15);

        $pendingInvitations = UserInvitation::whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        $teams = Team::orderBy('name')->get();

        return view('livewire.admin.user-manager', compact('users', 'pendingInvitations', 'teams'))
            ->layout('layouts.admin');
    }
}
