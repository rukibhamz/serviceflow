<?php

namespace App\Http\Controllers;

use App\Actions\Tickets\MergeTicketsAction;
use App\Models\Asset;
use App\Models\Automation;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Services\Asset\AssetImporter;
use App\Services\Asset\AssetService;
use App\Services\Problem\ProblemService;
use App\Services\Tickets\TicketStatusMachine;
use App\Services\Tickets\TicketSubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceActionController extends Controller
{
    public function saveAutomation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'editing_id' => 'nullable|integer|exists:automations,id',
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string',
            'cond_operator' => 'required|in:AND,OR',
            'conditions' => 'array',
            'conditions.*.field' => 'nullable|string',
            'conditions.*.op' => 'nullable|string',
            'conditions.*.value' => 'nullable|string',
            'actions' => 'array',
            'actions.*.type' => 'nullable|string',
            'actions.*.body' => 'nullable|string',
            'actions.*.status' => 'nullable|string',
            'actions.*.assignee_id' => 'nullable',
            'actions.*.url' => 'nullable|string',
            'actions.*.user_id' => 'nullable',
            'actions.*.message' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $payload = [
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'conditions' => [
                'operator' => $data['cond_operator'],
                'conditions' => array_values($data['conditions'] ?? []),
            ],
            'actions' => array_values($data['actions'] ?? []),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if (! empty($data['editing_id'])) {
            Automation::findOrFail($data['editing_id'])->update($payload);
            return back()->with('success', 'Automation updated.');
        }

        Automation::create($payload);
        return back()->with('success', 'Automation created.');
    }

    public function toggleAutomation(Automation $automation): RedirectResponse
    {
        $automation->is_active = ! $automation->is_active;
        $automation->save();

        return back()->with('success', 'Automation status updated.');
    }

    public function deleteAutomation(Automation $automation): RedirectResponse
    {
        $automation->delete();

        return back()->with('success', 'Automation deleted.');
    }

    public function saveAsset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'editing_id' => 'nullable|integer|exists:assets,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'asset_tag' => 'nullable|string|max:255',
            'status' => 'required|string',
            'purchased_at' => 'nullable|date',
        ]);

        $payload = [
            'name' => $data['name'],
            'type' => $data['type'],
            'serial_number' => $data['serial_number'] ?: null,
            'asset_tag' => $data['asset_tag'] ?: null,
            'status' => $data['status'],
            'purchased_at' => $data['purchased_at'] ?: null,
        ];

        $service = app(AssetService::class);

        if (! empty($data['editing_id'])) {
            $service->update(Asset::findOrFail($data['editing_id']), $payload);
            return back()->with('success', 'Asset updated.');
        }

        $service->create($payload);
        return back()->with('success', 'Asset created.');
    }

    public function importAssets(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $result = app(AssetImporter::class)->import($request->file('import_file')->getRealPath());
        $message = "Imported {$result->created} asset(s).";
        if (! empty($result->errors)) {
            $message .= ' Some rows failed validation.';
        }

        return back()->with('success', $message);
    }

    public function deleteAsset(Asset $asset): RedirectResponse
    {
        app(AssetService::class)->delete($asset);
        return back()->with('success', 'Asset deleted.');
    }

    public function unassignAsset(Asset $asset): RedirectResponse
    {
        app(AssetService::class)->unassign($asset);
        return back()->with('success', 'Asset unassigned.');
    }

    public function assignAsset(Request $request, Asset $asset): RedirectResponse
    {
        $data = $request->validate([
            'assignee_id' => 'required|integer|exists:users,id',
        ]);

        $user = \App\Models\User::findOrFail($data['assignee_id']);
        app(AssetService::class)->assign($asset, $user);

        return back()->with('success', 'Asset assigned.');
    }

    public function saveRootCause(Request $request, Ticket $problem): RedirectResponse
    {
        $this->authorizeTicketAccess($problem);
        abort_unless($problem->type === 'problem', 422);

        $data = $request->validate([
            'root_cause' => 'nullable|string',
            'workaround' => 'nullable|string',
            'mark_known_error' => 'nullable|boolean',
        ]);

        $service = app(ProblemService::class);
        $service->recordRootCause($problem, (string) ($data['root_cause'] ?? ''));
        if ((bool) ($data['mark_known_error'] ?? false)) {
            $service->markKnownError($problem, (string) ($data['workaround'] ?? ''));
        }

        return back()->with('success', 'Root cause saved.');
    }

    public function linkIncident(Ticket $problem, Ticket $incident): RedirectResponse
    {
        $this->authorizeTicketAccess($problem);
        $this->authorizeTicketAccess($incident);
        abort_unless($problem->type === 'problem', 422);
        abort_unless($incident->type === 'incident', 422);

        app(ProblemService::class)->linkIncidents($problem, [$incident->id]);
        return back()->with('success', 'Incident linked.');
    }

    public function unlinkIncident(Ticket $incident): RedirectResponse
    {
        $this->authorizeTicketAccess($incident);
        abort_unless($incident->type === 'incident', 422);

        app(ProblemService::class)->unlinkIncident($incident);
        return back()->with('success', 'Incident unlinked.');
    }

    public function addTicketComment(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicketAccess($ticket);

        $data = $request->validate([
            'comment_body' => 'required|string|min:1',
            'is_internal' => 'nullable|boolean',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $comment = $ticket->comments()->create([
            'user_id' => Auth::id(),
            'body' => $data['comment_body'],
            'is_internal' => (bool) ($data['is_internal'] ?? false),
        ]);

        foreach (($request->file('attachments') ?? []) as $file) {
            $comment->addMedia($file->getRealPath())
                ->usingName($file->getClientOriginalName())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('attachments');
        }

        return back()->with('success', 'Comment added.');
    }

    public function updateTicketStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicketAccess($ticket);

        $data = $request->validate([
            'status' => 'required|string|in:open,in_progress,pending,resolved,closed',
        ]);

        if ($ticket->status !== $data['status']) {
            app(TicketStatusMachine::class)->transition($ticket, $data['status']);
        }

        return back()->with('success', 'Status updated.');
    }

    public function updateTicketPriority(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicketAccess($ticket);

        $data = $request->validate([
            'priority' => 'required|string|in:low,medium,high,critical',
        ]);

        $ticket->update(['priority' => $data['priority']]);

        return back()->with('success', 'Priority updated.');
    }

    public function updateTicketAssignee(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicketAccess($ticket);

        $data = $request->validate([
            'assignee_id' => 'nullable|integer|exists:users,id',
        ]);

        $ticket->update(['assignee_id' => $data['assignee_id'] ?? null]);

        return back()->with('success', 'Assignee updated.');
    }

    public function mergeTicket(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicketAccess($ticket);

        $data = $request->validate([
            'merge_target_ulid' => 'required|string',
        ]);

        $target = Ticket::where('ulid', $data['merge_target_ulid'])->first();
        if (! $target) {
            return back()->with('error', 'Target ticket not found.');
        }
        if ($target->id === $ticket->id) {
            return back()->with('error', 'Cannot merge a ticket into itself.');
        }
        $this->authorizeTicketAccess($target);

        app(MergeTicketsAction::class)->execute($target, $ticket);

        $prefix = $request->is('admin/*')
            ? 'admin'
            : ($request->is('manager/*') ? 'manager' : ($request->is('team-lead/*') ? 'team-lead' : 'agent'));

        return redirect()->route($prefix . '.tickets.show', $target->ulid)->with('success', 'Ticket merged.');
    }

    public function toggleTicketWatch(Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicketAccess($ticket);

        $service = app(TicketSubscriptionService::class);
        if ($ticket->watchers()->where('user_id', Auth::id())->exists()) {
            $service->unsubscribe($ticket, Auth::id());
        } else {
            $service->subscribe($ticket, Auth::id());
        }

        return back();
    }

    private function authorizeTicketAccess(Ticket $ticket): void
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        if ($user->hasRole('admin') || $user->role === 'admin' || $user->hasRole('manager') || $user->role === 'manager') {
            return;
        }

        $teamId = (int) ($ticket->team_id ?? 0);
        $isRequester = (int) $ticket->requester_id === (int) $user->id;
        $isAssignee = (int) ($ticket->assignee_id ?? 0) === (int) $user->id;

        $isTeamLeadForTicketTeam = $teamId > 0 && Team::query()
            ->where('id', $teamId)
            ->where('team_lead_id', $user->id)
            ->exists();

        $isTeamMemberForTicketTeam = $teamId > 0 && Team::query()
            ->where('id', $teamId)
            ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->exists();

        abort_unless($isRequester || $isAssignee || $isTeamLeadForTicketTeam || $isTeamMemberForTicketTeam, 403);
    }
}

