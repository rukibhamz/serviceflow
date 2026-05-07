<?php

namespace App\Livewire\Tickets;

use App\Actions\Tickets\MergeTicketsAction;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Services\Ai\AiAssistService;
use App\Services\Tickets\TicketStatusMachine;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketResource extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $commentBody = '';
    public bool $isInternal = false;
    public $attachments = [];
    public string $newStatus = '';
    public string $newPriority = '';
    public string $newAssigneeId = '';
    public string $mergeTargetUlid = '';

    // ── Change / CAB ──────────────────────────────────────────────────────────
    public string $cabApproverSearch  = '';
    public bool   $showCabPanel       = false;
    public string $scheduledAt        = '';
    public string $changeType         = '';
    public string $riskLevel          = '';

    // ── AI Assist ─────────────────────────────────────────────────────────────
    public ?string $aiSummary     = null;
    public ?string $aiDraftReply  = null;
    public array   $aiSuggestions = [];
    public bool    $aiLoading     = false;
    public array   $otherViewers  = [];
    public string $routePrefix = 'agent';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->newStatus = $ticket->status;
        $this->newPriority = $ticket->priority;
        $this->newAssigneeId = (string) ($ticket->assignee_id ?? '');
        $this->changeType    = $ticket->change_type ?? 'normal';
        $this->riskLevel     = $ticket->risk_level ?? 'low';
        $this->scheduledAt   = $ticket->scheduled_at?->format('Y-m-d\TH:i') ?? '';
        if (request()->routeIs('admin.*')) {
            $this->routePrefix = 'admin';
        } elseif (request()->routeIs('manager.*')) {
            $this->routePrefix = 'manager';
        } elseif (request()->routeIs('team-lead.*')) {
            $this->routePrefix = 'team-lead';
        } else {
            $this->routePrefix = 'agent';
        }
        $this->keepAlive();
    }

    public function keepAlive(): void
    {
        if (Auth::check()) {
            $key = "ticket_viewing_{$this->ticket->id}_" . Auth::id();
            \Illuminate\Support\Facades\Cache::put($key, Auth::user()->name, 60);
        }

        $this->fetchOtherViewers();
    }

    protected function fetchOtherViewers(): void
    {
        $prefix = "ticket_viewing_{$this->ticket->id}_";
        $this->otherViewers = [];

        // This is a bit inefficient for high traffic, but works for internal ITSM
        // Better: Use Redis sets or a database table. For now, we'll use a simple cache pattern.
        // Since we can't easily glob cache keys in some drivers, we'll use a registry key.
        $registryKey = "ticket_viewers_registry_{$this->ticket->id}";
        $registry = \Illuminate\Support\Facades\Cache::get($registryKey, []);
        
        $now = now()->timestamp;
        $activeRegistry = [];
        
        foreach ($registry as $uid => $data) {
            if ($data['expires'] > $now) {
                if ($uid != Auth::id()) {
                    $this->otherViewers[] = $data['name'];
                }
                $activeRegistry[$uid] = $data;
            }
        }

        // Add self to registry
        $activeRegistry[Auth::id()] = [
            'name' => Auth::user()->name,
            'expires' => now()->addSeconds(65)->timestamp
        ];

        \Illuminate\Support\Facades\Cache::put($registryKey, $activeRegistry, 120);
    }


    public function addComment(): void
    {
        $this->validate([
            'commentBody' => 'required|string|min:1',
            'attachments.*' => 'nullable|file|max:10240', // 10MB limit per file
        ]);

        $comment = $this->ticket->comments()->create([
            'user_id'     => Auth::id(),
            'body'        => $this->commentBody,
            'is_internal' => $this->isInternal,
        ]);

        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $comment->addMedia($file->getRealPath())
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('attachments');
            }
        }

        $this->reset('commentBody', 'isInternal', 'attachments');
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

        $this->redirect(route($this->routePrefix . '.tickets.show', $target->ulid));
    }

    // ── AI Assist ─────────────────────────────────────────────────────────────

    public function aiSummarise(): void
    {
        $this->aiLoading = true;
        try {
            $result = app(AiAssistService::class)->summarise($this->ticket);
            $this->aiSummary = $result;
            if ($this->looksLikeAiError($result)) {
                session()->flash('error', $result);
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'AI summary failed: ' . $e->getMessage());
        } finally {
            $this->aiLoading = false;
        }
    }

    public function aiDraft(): void
    {
        $this->aiLoading = true;
        try {
            $result = app(AiAssistService::class)->draftReply($this->ticket);
            $this->aiDraftReply = $result;
            if ($this->looksLikeAiError($result)) {
                session()->flash('error', $result);
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'AI draft failed: ' . $e->getMessage());
        } finally {
            $this->aiLoading = false;
        }
    }

    public function aiSuggestArticles(): void
    {
        $this->aiLoading = true;
        try {
            $result = app(AiAssistService::class)->suggestArticles($this->ticket);
            $this->aiSuggestions = $result;
            if (empty($result)) {
                session()->flash('error', 'AI suggestion returned no results. Check AI configuration.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'AI suggestions failed: ' . $e->getMessage());
        } finally {
            $this->aiLoading = false;
        }
    }

    public function useAiDraft(): void
    {
        if (! $this->aiDraftReply) {
            session()->flash('error', 'No AI draft available to use.');
            return;
        }
        $this->commentBody  = $this->aiDraftReply ?? '';
        $this->aiDraftReply = null;
    }

    private function looksLikeAiError(string $text): bool
    {
        return str_starts_with($text, '[AI Assist');
    }

    public function toggleSubscription(): void
    {
        $service = app(\App\Services\Tickets\TicketSubscriptionService::class);
        if ($this->ticket->watchers()->where('user_id', Auth::id())->exists()) {
            $service->unsubscribe($this->ticket, Auth::id());
        } else {
            $service->subscribe($this->ticket, Auth::id());
        }
        $this->ticket->refresh();
    }

    // ── Change / CAB ──────────────────────────────────────────────────────────

    public function saveChangeDetails(): void
    {
        $this->ticket->update([
            'change_type' => $this->changeType ?: null,
            'risk_level'  => $this->riskLevel ?: null,
            'scheduled_at'=> $this->scheduledAt ?: null,
            'cab_approval_required' => true,
        ]);
        $this->ticket->refresh();
        session()->flash('success', 'Change details saved.');
    }

    public function addCabApprover(int $userId): void
    {
        if ($this->ticket->changeApprovers()->where('user_id', $userId)->exists()) {
            session()->flash('error', 'This user is already an approver.');
            return;
        }

        $approver = $this->ticket->changeApprovers()->create([
            'user_id' => $userId,
            'token'   => \Illuminate\Support\Str::random(40),
        ]);

        $brandName = app(\App\Services\SettingService::class)->get('brand_name', config('app.name', 'ServiceFlow'));
        $approver->load('user', 'ticket.requester');

        \Illuminate\Support\Facades\Mail::to($approver->user->email)
            ->send(new \App\Mail\ChangeApprovalRequestMail($approver, $brandName));

        $this->cabApproverSearch = '';
        $this->ticket->refresh();
        session()->flash('success', "Approval request sent to {$approver->user->name}.");
    }

    public function removeCabApprover(int $approverId): void
    {
        $this->ticket->changeApprovers()->findOrFail($approverId)->delete();
        $this->ticket->refresh();
        session()->flash('success', 'Approver removed.');
    }

    public function submitForApproval(): void
    {
        if ($this->ticket->changeApprovers()->doesntExist()) {
            session()->flash('error', 'Add at least one approver before submitting.');
            return;
        }

        try {
            app(\App\Services\Change\ChangeApprovalWorkflow::class)->submitForApproval($this->ticket);
            $this->ticket->refresh();
            $this->newStatus = $this->ticket->status;
            session()->flash('success', 'Change submitted for CAB approval. Approvers have been notified.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }


    public function render()
    {
        $this->ticket->load(['requester', 'assignee', 'team', 'slaTimers', 'comments.author', 'changeApprovers.user']);

        $agents   = \App\Models\User::orderBy('name')->get(['id', 'name']);
        $statuses = $this->ticket->type === 'change'
            ? array_keys(\App\Services\Change\ChangeApprovalWorkflow::TRANSITIONS ?? [])
            : (new TicketStatusMachine)->validStatuses();

        // CAB approver search
        $cabApproverResults = collect();
        if ($this->cabApproverSearch) {
            $existingIds = $this->ticket->changeApprovers()->pluck('user_id');
            $cabApproverResults = \App\Models\User::where(function ($q) {
                $q->where('name', 'like', "%{$this->cabApproverSearch}%")
                  ->orWhere('email', 'like', "%{$this->cabApproverSearch}%");
            })->whereNotIn('id', $existingIds)->limit(8)->get();
        }

        return view('livewire.tickets.ticket-resource', compact('agents', 'statuses', 'cabApproverResults'));
    }
}
