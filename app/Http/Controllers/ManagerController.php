<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Models\ServiceCatalogueItem;

class ManagerController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'teams' => Team::count(),
            'team_leads' => User::where('role', 'team_lead')->count(),
            'agents' => User::where('role', 'agent')->count(),
            'open_tickets' => Ticket::whereNotIn('status', ['resolved', 'closed'])->count(),
            'changes' => Ticket::where('type', 'change')->count(),
            'problems' => Ticket::where('type', 'problem')->count(),
            'catalogue_active' => ServiceCatalogueItem::query()->where('is_active', true)->count(),
        ];

        $recentTickets = Ticket::with(['requester', 'team'])
            ->latest()
            ->take(12)
            ->get();

        $recentCatalogue = ServiceCatalogueItem::query()
            ->with('team')
            ->latest('id')
            ->take(8)
            ->get();

        return view('manager.dashboard', compact('stats', 'recentTickets', 'recentCatalogue'));
    }

    public function teams()
    {
        $teams = Team::with(['lead', 'members'])->latest('id')->get();
        return view('manager.teams', compact('teams'));
    }

    public function users()
    {
        $users = User::with('teams')->latest()->paginate(20);
        return view('manager.users', compact('users'));
    }

    public function tickets()
    {
        $tickets = Ticket::with(['requester', 'assignee', 'team'])->latest()->paginate(20);
        return view('manager.tickets', compact('tickets'));
    }

    public function showTicket(Ticket $ticket)
    {
        return view('manager.tickets.show', compact('ticket'));
    }
}

