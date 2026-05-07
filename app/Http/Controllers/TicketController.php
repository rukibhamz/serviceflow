<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Change\ChangeApprovalWorkflow;
use App\Services\SettingService;
use App\Services\Tickets\TicketStatusMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high,urgent',
            'type' => 'required|in:incident,service_request,problem,change',
            'requester_id' => 'required|exists:users,id',
            'team_id' => 'nullable|exists:teams,id',
            'change_approver_ids' => 'nullable|array',
            'change_approver_ids.*' => 'integer|exists:users,id',
            'submit_for_approval' => 'nullable|boolean',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx|max:10240',
        ]);

        if (in_array($data['type'], ['problem', 'change'], true) && empty($data['team_id'])) {
            return back()->withErrors(['team_id' => 'Team is required for problems and change requests.'])->withInput();
        }
        if (($data['type'] ?? null) === 'change' && (bool) ($data['submit_for_approval'] ?? false) && empty($data['change_approver_ids'])) {
            return back()->withErrors(['change_approver_ids' => 'Select at least one approver to request change approval.'])->withInput();
        }

        $ticket = Ticket::create([
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'type' => $data['type'],
            'requester_id' => $data['requester_id'] ?: Auth::id(),
            'team_id' => $data['team_id'] ?? null,
            'status' => 'open',
            'cab_approval_required' => $data['type'] === 'change' && ! empty($data['change_approver_ids']),
            'source' => 'web',
        ]);

        foreach (($request->file('attachments') ?? []) as $file) {
            $ticket->addMedia($file->getRealPath())
                ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->usingFileName($file->hashName())
                ->toMediaCollection('attachments');
        }

        app(\App\Services\Sla\SlaService::class)->assignPolicy($ticket);
        app(\App\Services\Tickets\TicketAssignmentService::class)->autoAssign($ticket);
        $this->createChangeApprovers($ticket, $data);

        $routePrefix = $request->is('admin/*') ? 'admin' : 'agent';
        return redirect()->route($routePrefix . '.tickets.show', $ticket)
            ->with('message', 'Ticket created successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket): Response
    {
        $data = $request->validate([
            'status' => 'required|string|in:open,in_progress,pending,resolved,closed',
        ]);

        if ($ticket->status !== $data['status']) {
            app(TicketStatusMachine::class)->transition($ticket, $data['status']);
        }

        return response()->noContent();
    }

    private function createChangeApprovers(Ticket $ticket, array $data): void
    {
        if (($data['type'] ?? null) !== 'change' || empty($data['change_approver_ids'])) {
            return;
        }

        $brandName = app(SettingService::class)->get('brand_name', config('app.name', 'ServiceFlow'));
        $approverIds = collect($data['change_approver_ids'])->map(fn ($id) => (int) $id)->unique()->values();

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

        if ((bool) ($data['submit_for_approval'] ?? false)) {
            app(ChangeApprovalWorkflow::class)->submitForApproval($ticket);
        }
    }
}

