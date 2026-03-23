<?php

namespace App\Services\Change;

use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Ticket;

/**
 * Manages the CAB approval state machine for change tickets.
 *
 * Change tickets that require CAB approval follow this flow:
 *   open → pending_approval → approved → in_progress → resolved → closed
 *                           ↘ rejected → open (re-submit)
 *
 * Tickets that do NOT require CAB approval skip straight to the standard
 * TicketStatusMachine flow.
 */
class ChangeApprovalWorkflow
{
    /** Allowed transitions within the CAB approval sub-graph */
    private const TRANSITIONS = [
        'open'             => ['pending_approval', 'in_progress'],
        'pending_approval' => ['approved', 'rejected'],
        'approved'         => ['in_progress', 'scheduled'],
        'scheduled'        => ['in_progress'],
        'rejected'         => ['open'],
        'in_progress'      => ['resolved'],
        'resolved'         => ['closed', 'open'],
        'closed'           => ['open'],
    ];

    /**
     * Transition a change ticket to a new status.
     *
     * @throws InvalidStatusTransitionException
     */
    public function transition(Ticket $ticket, string $newStatus): Ticket
    {
        $current = $ticket->status;

        $allowed = self::TRANSITIONS[$current] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition change ticket from '{$current}' to '{$newStatus}'."
            );
        }

        $ticket->status = $newStatus;
        $ticket->save();

        return $ticket;
    }

    /**
     * Submit a change ticket for CAB approval.
     *
     * @throws InvalidStatusTransitionException
     */
    public function submitForApproval(Ticket $ticket): Ticket
    {
        if (! $ticket->cab_approval_required) {
            throw new \LogicException('This change ticket does not require CAB approval.');
        }

        return $this->transition($ticket, 'pending_approval');
    }

    /**
     * Approve a change ticket.
     *
     * @throws InvalidStatusTransitionException
     */
    public function approve(Ticket $ticket): Ticket
    {
        return $this->transition($ticket, 'approved');
    }

    /**
     * Reject a change ticket, returning it to open for revision.
     *
     * @throws InvalidStatusTransitionException
     */
    public function reject(Ticket $ticket): Ticket
    {
        return $this->transition($ticket, 'rejected');
    }

    /**
     * Schedule an approved change for a specific time.
     */
    public function schedule(Ticket $ticket, \DateTimeInterface $scheduledAt): Ticket
    {
        $ticket = $this->transition($ticket, 'scheduled');
        $ticket->scheduled_at = $scheduledAt;
        $ticket->save();

        return $ticket;
    }

    /**
     * Returns whether the given status is a valid CAB approval state.
     */
    public function isValidStatus(string $status): bool
    {
        return array_key_exists($status, self::TRANSITIONS);
    }
}
