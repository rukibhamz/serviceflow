<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Ticket;
use App\Models\ServiceCatalogueItem;
use Illuminate\Support\Facades\Auth;

class TeamLeadController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $teamIds = Team::where('team_lead_id', $user->id)->pluck('id');

        $stats = [
            'teams' => $teamIds->count(),
            'members' => \DB::table('team_user')->whereIn('team_id', $teamIds)->distinct('user_id')->count('user_id'),
            'open_tickets' => Ticket::whereIn('team_id', $teamIds)->whereNotIn('status', ['resolved', 'closed'])->count(),
            'changes' => Ticket::whereIn('team_id', $teamIds)->where('type', 'change')->count(),
            'problems' => Ticket::whereIn('team_id', $teamIds)->where('type', 'problem')->count(),
            'catalogue_active' => ServiceCatalogueItem::isAvailable()
                ? ServiceCatalogueItem::query()
                    ->where('is_active', true)
                    ->whereIn('team_id', $teamIds)
                    ->count()
                : 0,
        ];

        $recentTickets = Ticket::with(['requester', 'team'])
            ->whereIn('team_id', $teamIds)
            ->latest()
            ->take(10)
            ->get();

        $recentCatalogue = ServiceCatalogueItem::isAvailable()
            ? ServiceCatalogueItem::query()
                ->with('team')
                ->whereIn('team_id', $teamIds)
                ->latest('id')
                ->take(8)
                ->get()
            : collect();

        return view('team-lead.dashboard', compact('stats', 'recentTickets', 'recentCatalogue'));
    }

    public function teams()
    {
        $teams = Team::with(['members', 'lead'])
            ->where('team_lead_id', Auth::id())
            ->latest('id')
            ->get();

        return view('team-lead.teams', compact('teams'));
    }

    public function tickets()
    {
        $teamIds = Team::where('team_lead_id', Auth::id())->pluck('id');
        $tickets = Ticket::with(['requester', 'assignee', 'team'])
            ->whereIn('team_id', $teamIds)
            ->latest()
            ->paginate(20);

        return view('team-lead.tickets', compact('tickets'));
    }

    public function showTicket(Ticket $ticket)
    {
        $teamIds = Team::where('team_lead_id', Auth::id())->pluck('id');
        abort_unless($teamIds->contains($ticket->team_id), 403);

        return view('team-lead.tickets.show', compact('ticket'));
    }
}

