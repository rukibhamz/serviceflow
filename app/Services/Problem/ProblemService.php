<?php

namespace App\Services\Problem;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

/**
 * Manages Problem tickets and their linked incident tickets.
 *
 * A "problem" is a ticket of type `problem`. Incidents are linked to a problem
 * via the `problem_id` foreign key on the tickets table.
 */
class ProblemService
{
    /**
     * Link one or more incident tickets to a problem ticket.
     *
     * @param  Ticket    $problem   Must be type=problem
     * @param  int[]     $incidentIds
     */
    public function linkIncidents(Ticket $problem, array $incidentIds): void
    {
        $this->assertProblem($problem);

        Ticket::whereIn('id', $incidentIds)
            ->where('type', 'incident')
            ->update(['problem_id' => $problem->id]);
    }

    /**
     * Unlink an incident from its problem.
     */
    public function unlinkIncident(Ticket $incident): void
    {
        $incident->problem_id = null;
        $incident->save();
    }

    /**
     * Get all incidents linked to a problem.
     */
    public function linkedIncidents(Ticket $problem): Collection
    {
        $this->assertProblem($problem);

        return Ticket::where('problem_id', $problem->id)
            ->where('type', 'incident')
            ->get();
    }

    /**
     * Record the root cause on a problem ticket (stored in custom_fields).
     */
    public function recordRootCause(Ticket $problem, string $rootCause): Ticket
    {
        $this->assertProblem($problem);

        $fields = $problem->custom_fields ?? [];
        $fields['root_cause'] = $rootCause;
        $problem->custom_fields = $fields;
        $problem->save();

        return $problem;
    }

    /**
     * Mark a problem as a Known Error (stored in custom_fields).
     */
    public function markKnownError(Ticket $problem, string $workaround = ''): Ticket
    {
        $this->assertProblem($problem);

        $fields = $problem->custom_fields ?? [];
        $fields['known_error']  = true;
        $fields['workaround']   = $workaround;
        $problem->custom_fields = $fields;
        $problem->save();

        return $problem;
    }

    /**
     * Aggregate incident count and open/closed breakdown for a problem.
     */
    public function aggregateStats(Ticket $problem): array
    {
        $this->assertProblem($problem);

        $incidents = $this->linkedIncidents($problem);

        return [
            'total'  => $incidents->count(),
            'open'   => $incidents->whereNotIn('status', ['resolved', 'closed'])->count(),
            'closed' => $incidents->whereIn('status', ['resolved', 'closed'])->count(),
        ];
    }

    private function assertProblem(Ticket $ticket): void
    {
        if ($ticket->type !== 'problem') {
            throw new \InvalidArgumentException("Ticket #{$ticket->id} is not a problem ticket.");
        }
    }
}
