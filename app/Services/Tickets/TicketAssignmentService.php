<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketAssignmentService
{
    /**
     * Automatically assign a ticket to an agent based on Round-Robin.
     */
    public function autoAssign(Ticket $ticket): void
    {
        // If already assigned, skip
        if ($ticket->assignee_id) {
            return;
        }

        // Find the next available agent (oldest last_assigned_at)
        // We filter by 'agent' or 'admin' role if using Spatie
        $agent = User::role(['agent', 'admin'])
            ->where('is_active', true)
            ->orderBy('last_assigned_at', 'asc') // This will put NULLs first, which is fine
            ->first();

        if ($agent) {
            DB::transaction(function () use ($ticket, $agent) {
                $ticket->update(['assignee_id' => $agent->id]);
                $agent->update(['last_assigned_at' => now()]);
            });
        }
    }

    /**
     * Assign to a specific team's next available agent.
     */
    public function assignToTeam(Ticket $ticket, int $teamId): void
    {
        $agent = User::role(['agent', 'admin'])
            ->where('is_active', true)
            ->whereHas('teams', function ($query) use ($teamId) {
                $query->where('teams.id', $teamId);
            })
            ->orderBy('last_assigned_at', 'asc')
            ->first();

        if ($agent) {
            DB::transaction(function () use ($ticket, $agent) {
                $ticket->update(['assignee_id' => $agent->id]);
                $agent->update(['last_assigned_at' => now()]);
            });
        }
    }
}
