<?php

namespace App\Services\Reports;

use App\Models\Asset;
use App\Models\CsatSurvey;
use App\Models\SlaTimer;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds standard report datasets.
 *
 * All methods accept an optional date range ($from / $to).
 * Results are returned as plain arrays/Collections for easy export.
 */
class ReportBuilder
{
    // ── Ticket Volume ─────────────────────────────────────────────────────────

    /**
     * Ticket counts grouped by status, priority, and type for the given period.
     *
     * @return array{by_status: Collection, by_priority: Collection, by_type: Collection, total: int}
     */
    public function ticketVolume(?Carbon $from = null, ?Carbon $to = null): array
    {
        $base = Ticket::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->where('created_at', '<=', $to));

        return [
            'total'       => (clone $base)->count(),
            'by_status'   => (clone $base)->selectRaw('status, count(*) as count')->groupBy('status')->get(),
            'by_priority' => (clone $base)->selectRaw('priority, count(*) as count')->groupBy('priority')->get(),
            'by_type'     => (clone $base)->selectRaw('type, count(*) as count')->groupBy('type')->get(),
        ];
    }

    // ── SLA Compliance ────────────────────────────────────────────────────────

    /**
     * SLA compliance rate: percentage of timers that did NOT breach.
     *
     * @return array{total: int, breached: int, compliant: int, compliance_rate: float}
     */
    public function slaCompliance(?Carbon $from = null, ?Carbon $to = null): array
    {
        $base = SlaTimer::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->where('created_at', '<=', $to));

        $total    = (clone $base)->count();
        $breached = (clone $base)->where('breached', true)->count();
        $compliant = $total - $breached;

        return [
            'total'           => $total,
            'breached'        => $breached,
            'compliant'       => $compliant,
            'compliance_rate' => $total > 0 ? round(($compliant / $total) * 100, 2) : 100.0,
        ];
    }

    // ── Agent Performance ─────────────────────────────────────────────────────

    /**
     * Per-agent ticket counts and average resolution time (minutes).
     *
     * @return Collection<int, object{agent_id: int, name: string, assigned: int, resolved: int, avg_resolution_minutes: float}>
     */
    public function agentPerformance(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        return Ticket::query()
            ->join('users', 'users.id', '=', 'tickets.assignee_id')
            ->selectRaw('
                tickets.assignee_id,
                users.name,
                count(*) as assigned,
                sum(case when tickets.status in (\'resolved\',\'closed\') then 1 else 0 end) as resolved,
                avg(case when tickets.closed_at is not null
                    then timestampdiff(minute, tickets.created_at, tickets.closed_at)
                    else null end) as avg_resolution_minutes
            ')
            ->when($from, fn ($q) => $q->where('tickets.created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->where('tickets.created_at', '<=', $to))
            ->whereNotNull('tickets.assignee_id')
            ->groupBy('tickets.assignee_id', 'users.name')
            ->orderByDesc('assigned')
            ->get();
    }

    // ── CSAT Scores ───────────────────────────────────────────────────────────

    /**
     * CSAT summary: average rating, response rate, distribution.
     *
     * @return array{average: float, response_rate: float, total_sent: int, total_responded: int, distribution: Collection}
     */
    public function csatScores(?Carbon $from = null, ?Carbon $to = null): array
    {
        $base = CsatSurvey::query()
            ->when($from, fn ($q) => $q->where('sent_at', '>=', $from))
            ->when($to,   fn ($q) => $q->where('sent_at', '<=', $to));

        $totalSent      = (clone $base)->count();
        $totalResponded = (clone $base)->whereNotNull('responded_at')->count();
        $average        = (clone $base)->whereNotNull('rating')->avg('rating') ?? 0.0;

        $distribution = (clone $base)
            ->whereNotNull('rating')
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();

        return [
            'average'         => round((float) $average, 2),
            'response_rate'   => $totalSent > 0 ? round(($totalResponded / $totalSent) * 100, 2) : 0.0,
            'total_sent'      => $totalSent,
            'total_responded' => $totalResponded,
            'distribution'    => $distribution,
        ];
    }

    // ── Asset Inventory ───────────────────────────────────────────────────────

    /**
     * Asset counts grouped by type and status.
     *
     * @return array{by_type: Collection, by_status: Collection, total: int}
     */
    public function assetInventory(): array
    {
        return [
            'total'     => Asset::count(),
            'by_type'   => Asset::selectRaw('type, count(*) as count')->groupBy('type')->get(),
            'by_status' => Asset::selectRaw('status, count(*) as count')->groupBy('status')->get(),
        ];
    }
}
