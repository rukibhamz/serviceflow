<?php

namespace App\Livewire\Dashboard;

use App\Models\SlaTimer;
use App\Models\Ticket;
use App\Models\Team;
use App\Services\Reports\ReportBuilder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class DashboardWidgets extends Component
{
    public array $counters        = [];
    public array $volumeChart     = [];
    public array $typeChart       = [];
    public array $csatChart       = [];
    public array $teamPerformance = [];
    public array $recentActivity  = [];
    public string $mttr           = '0h';

    public function mount(): void { $this->refresh(); }

    #[On('echo:tickets,TicketUpdated')]
    #[On('echo:tickets,SlaBreached')]
    public function refresh(): void
    {
        $builder = app(ReportBuilder::class);
        $this->counters        = $this->buildCounters();
        $this->volumeChart     = $this->buildVolumeChart();
        $this->typeChart       = $this->buildTypeChart($builder);
        $this->csatChart       = $this->buildCsatChart($builder);
        $this->teamPerformance = $this->buildTeamPerformance();
        $this->recentActivity  = $this->buildRecentActivity();
        $this->mttr            = $this->calculateMttr($builder);
    }

    private function buildCounters(): array
    {
        $uid = auth()->id();
        return [
            'open'           => Ticket::whereNotIn('status', ['resolved','closed'])->count(),
            'resolved_today' => Ticket::whereIn('status', ['resolved','closed'])->whereDate('updated_at', today())->count(),
            'breached'       => SlaTimer::where('breached', true)
                ->whereHas('ticket', fn($q) => $q->whereNotIn('status', ['resolved','closed']))->count(),
            'my_open'        => Ticket::where('assignee_id', $uid)->whereNotIn('status', ['resolved','closed'])->count(),
        ];
    }

    private function buildVolumeChart(): array
    {
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $counts = Ticket::selectRaw('DATE(created_at) as day, count(*) as cnt')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')->pluck('cnt', 'day');

        return [
            'labels' => $days->map(fn($d) => now()->parse($d)->format('D'))->values()->toArray(),
            'data'   => $days->map(fn($d) => $counts[$d] ?? 0)->values()->toArray(),
        ];
    }

    private function buildTypeChart(ReportBuilder $builder): array
    {
        $vol = $builder->ticketVolume(now()->subDays(30));
        $total = max($vol['total'], 1);
        $labels = $data = $pcts = [];
        foreach ($vol['by_type'] as $row) {
            $labels[] = ucfirst(str_replace('_', ' ', $row->type ?? 'Other'));
            $data[]   = (int) $row->count;
            $pcts[]   = round($row->count / $total * 100) . '%';
        }
        return compact('labels', 'data', 'pcts');
    }

    private function buildTeamPerformance(): array
    {
        $teams = Team::withCount([
            'tickets as open_count'     => fn($q) => $q->whereNotIn('status', ['resolved','closed']),
            'tickets as resolved_count' => fn($q) => $q->whereIn('status', ['resolved','closed']),
            'tickets as total_count',
        ])->get();

        return $teams->map(function ($team) {
            $total    = max($team->total_count, 1);
            $sla_pct  = $team->total_count > 0
                ? round($team->resolved_count / $total * 100)
                : 100;
            $status = $sla_pct >= 85 ? 'on_track' : ($sla_pct >= 65 ? 'at_risk' : 'breached');
            return [
                'name'     => $team->name,
                'open'     => $team->open_count,
                'resolved' => $team->resolved_count,
                'sla_pct'  => $sla_pct,
                'status'   => $status,
            ];
        })->toArray();
    }

    private function buildRecentActivity(): array
    {
        return \Spatie\Activitylog\Models\Activity::with('causer')
            ->latest()->limit(8)->get()
            ->map(fn($a) => [
                'description' => $a->description,
                'subject'     => optional($a->subject)->subject ?? optional($a->subject)->name ?? '',
                'causer'      => optional($a->causer)->name ?? 'System',
                'time'        => $a->created_at->diffForHumans(),
                'type'        => $a->log_name ?? 'default',
            ])->toArray();
    }

    private function buildCsatChart(ReportBuilder $builder): array
    {
        $report = $builder->csatScores(now()->subDays(30));
        $labels = $data = [];
        foreach ($report['distribution'] as $row) {
            $labels[] = '★ ' . $row->rating;
            $data[]   = $row->count;
        }
        return ['labels' => $labels, 'data' => $data, 'average' => $report['average']];
    }

    private function calculateMttr(ReportBuilder $builder): string
    {
        $perf = $builder->agentPerformance(now()->subDays(30));
        $mine = $perf->firstWhere('assignee_id', auth()->id());
        $mins = $mine->avg_resolution_minutes ?? 0;
        if (!$mins) return '0h';
        $h = floor($mins / 60); $m = $mins % 60;
        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-widgets');
    }
}
