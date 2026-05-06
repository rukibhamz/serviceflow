<?php

namespace App\Livewire\Problem;

use App\Models\Ticket;
use App\Services\Problem\ProblemService;
use Livewire\Component;
use Livewire\WithPagination;

class ProblemList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    // Link incidents panel
    public ?int $linkingId = null;
    public string $incidentSearch = '';

    // Root cause panel
    public ?int $rootCauseId = null;
    public string $rootCause = '';
    public string $workaround = '';
    public bool $markKnownError = false;

    public function updatingSearch(): void { $this->resetPage(); }

    public function openLinkPanel(int $id): void
    {
        $this->linkingId = $id;
        $this->incidentSearch = '';
    }

    public function linkIncident(int $incidentId): void
    {
        $problem = Ticket::findOrFail($this->linkingId);
        app(ProblemService::class)->linkIncidents($problem, [$incidentId]);
        session()->flash('success', 'Incident linked.');
    }

    public function unlinkIncident(int $incidentId): void
    {
        $incident = Ticket::findOrFail($incidentId);
        app(ProblemService::class)->unlinkIncident($incident);
        session()->flash('success', 'Incident unlinked.');
    }

    public function openRootCause(int $id): void
    {
        $problem = Ticket::findOrFail($id);
        $this->rootCauseId    = $id;
        $this->rootCause      = $problem->custom_fields['root_cause'] ?? '';
        $this->workaround     = $problem->custom_fields['workaround'] ?? '';
        $this->markKnownError = $problem->custom_fields['known_error'] ?? false;
    }

    public function saveRootCause(): void
    {
        $problem = Ticket::findOrFail($this->rootCauseId);
        $service = app(ProblemService::class);
        $service->recordRootCause($problem, $this->rootCause);

        if ($this->markKnownError) {
            $service->markKnownError($problem, $this->workaround);
        }

        $this->rootCauseId = null;
        session()->flash('success', 'Root cause saved.');
    }

    public function render()
    {
        $problems = Ticket::where('type', 'problem')
            ->with('requester', 'assignee')
            ->when($this->search, fn ($q) => $q->where('subject', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        $incidentResults = collect();
        if ($this->linkingId && $this->incidentSearch) {
            $incidentResults = Ticket::where('type', 'incident')
                ->whereNull('problem_id')
                ->where('subject', 'like', "%{$this->incidentSearch}%")
                ->limit(10)
                ->get();
        }

        $linkedIncidents = $this->linkingId
            ? Ticket::where('problem_id', $this->linkingId)->get()
            : collect();

        return view('livewire.problem.problem-list', compact(
            'problems', 'incidentResults', 'linkedIncidents'
        ));
    }
}
