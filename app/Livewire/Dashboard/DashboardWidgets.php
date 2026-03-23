<?php

namespace App\Livewire\Dashboard;

use App\Models\SlaTimer;
use App\Models\Ticket;
use App\Services\Reports\ReportBuilder;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Dashboard widget component — real-time counters and chart data.
 *
 * Refreshes automatically when TicketUpdated or SlaBreached events
 * are broadcast over Reverb (via the 'ticket-updated' and 'sla-breached'
 * browser events dispatched by the Livewire echo listeners).
 */
class DashboardWidgets extends Component
{
    public array $counters = [];
    public array $volumeChart = [];
    public array $csatChart   = [];

    public function mount(): void
    {
        $this->refresh();
    }

    #[On('echo:tickets,TicketUpdated')]
    #[On('echo:tickets,SlaBreached')]
    public function refresh(): void
    {
        $this->counters = $this->buildCounters();
        $this->volumeChart = $this->buildVolumeChart();
        $this->csatChart   = $this->buildCsatChart();
    }

    private function buildCounters(): array
    {
        return [
            'open'       => Ticket::whereNotIn('status', ['resolved', 'closed'])->count(),
            'unassigned' => Ticket::whereNull('assignee_id')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'breached'   => SlaTimer::where('breached', true)
                ->whereHas('ticket', fn ($q) => $q->whereNotIn('status', ['resolved', 'closed']))
                ->count(),
            'resolved_today' => Ticket::whereIn('status', ['resolved', 'closed'])
                ->whereDate('closed_at', today())
                ->count(),
        ];
    }

    private function buildVolumeChart(): array
    {
        $builder = app(ReportBuilder::class);
        $report  = $builder->ticketVolume(now()->subDays(30));

        $labels = [];
        $data   = [];

        foreach ($report['by_status'] as $row) {
            $labels[] = $row->status;
            $data[]   = $row->count;
        }

        return compact('labels', 'data');
    }

    private function buildCsatChart(): array
    {
        $builder = app(ReportBuilder::class);
        $report  = $builder->csatScores(now()->subDays(30));

        $labels = [];
        $data   = [];

        foreach ($report['distribution'] as $row) {
            $labels[] = '★ ' . $row->rating;
            $data[]   = $row->count;
        }

        return [
            'labels'  => $labels,
            'data'    => $data,
            'average' => $report['average'],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-widgets');
    }
}
