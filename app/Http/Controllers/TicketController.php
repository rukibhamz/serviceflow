<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\Tickets\TicketStatusMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx|max:10240',
        ]);

        if (in_array($data['type'], ['problem', 'change'], true) && empty($data['team_id'])) {
            return back()->withErrors(['team_id' => 'Team is required for problems and change requests.'])->withInput();
        }

        $ticket = Ticket::create([
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'type' => $data['type'],
            'requester_id' => $data['requester_id'] ?: Auth::id(),
            'team_id' => $data['team_id'] ?? null,
            'status' => 'open',
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
}

