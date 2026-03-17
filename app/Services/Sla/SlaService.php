<?php

namespace App\Services\Sla;

use App\Events\SlaBreached;
use App\Models\SlaPolicy;
use App\Models\SlaTimer;
use App\Models\Ticket;
use App\Models\TicketComment;
use Carbon\Carbon;

class SlaService
{
    public function __construct(private BusinessHoursCalculator $calculator) {}

    public function assignPolicy(Ticket $ticket): void
    {
        // Match by priority + optional ticket_type (specific match wins over null)
        $policy = SlaPolicy::where('priority', $ticket->priority)
            ->where(function ($q) use ($ticket) {
                $q->where('ticket_type', $ticket->type)
                  ->orWhereNull('ticket_type');
            })
            ->where('is_active', true)
            ->orderByRaw('ticket_type IS NULL ASC')
            ->first();

        // Fall back to default policy
        if (! $policy) {
            $policy = SlaPolicy::where('is_default', true)->first();
        }

        if (! $policy) {
            return;
        }

        $now = Carbon::now();
        $schedule = $policy->business_hours; // null means 24/7

        foreach (['response' => $policy->response_minutes, 'resolution' => $policy->resolution_minutes] as $type => $minutes) {
            if ($minutes === null) {
                continue;
            }

            $dueAt = $schedule
                ? $this->calculator->addBusinessMinutes($now, $minutes, $schedule)
                : $now->copy()->addMinutes($minutes);

            SlaTimer::create([
                'ticket_id'     => $ticket->id,
                'sla_policy_id' => $policy->id,
                'type'          => $type,
                'due_at'        => $dueAt,
                'breached'      => false,
            ]);
        }
    }

    public function recordFirstResponse(Ticket $ticket, TicketComment $comment): void
    {
        if ($comment->is_internal) {
            return;
        }

        $timer = SlaTimer::where('ticket_id', $ticket->id)
            ->where('type', 'response')
            ->whereNull('stopped_at')
            ->first();

        if ($timer) {
            $timer->stopped_at = Carbon::now();
            $timer->save();
        }
    }

    public function checkBreach(SlaTimer $timer): bool
    {
        if ($timer->stopped_at !== null) {
            return false;
        }

        if ($timer->breached) {
            return false;
        }

        if (Carbon::now()->gt($timer->due_at)) {
            $timer->breached = true;
            $timer->save();

            event(new SlaBreached($timer));

            return true;
        }

        return false;
    }
}
