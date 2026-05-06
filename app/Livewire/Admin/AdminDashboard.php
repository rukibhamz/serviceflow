<?php

namespace App\Livewire\Admin;

use App\Models\SlaTimer;
use App\Models\Ticket;
use App\Services\Reports\ReportBuilder;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminDashboard extends Component
{
    public array $globalStats = [];
    public array $slaCompliance = [];
    public array $teamVolume = [];

    public function mount(): void
    {
        $this->refresh();
    }

    #[On('echo:tickets,TicketUpdated')]
    #[On('echo:tickets,SlaBreached')]
    public function refresh(): void
    {
        $reportBuilder = app(ReportBuilder::class);
        
        $this->globalStats = [
            'total_open' => Ticket::whereNotIn('status', ['resolved', 'closed'])->count(),
            'unassigned' => Ticket::whereNull('assignee_id')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'breached'   => SlaTimer::where('breached', true)
                ->whereHas('ticket', fn ($q) => $q->whereNotIn('status', ['resolved', 'closed']))
                ->count(),
            'avg_mttr'   => $this->calculateGlobalMttr($reportBuilder),
        ];

        $this->slaCompliance = $reportBuilder->slaCompliance(now()->subDays(30));
        
        $volumeReport = $reportBuilder->ticketVolume(now()->subDays(30));
        $this->teamVolume = [
            'labels' => $volumeReport['by_type']->pluck('type')->toArray(),
            'data'   => $volumeReport['by_type']->pluck('count')->toArray(),
        ];
    }

    private function calculateGlobalMttr(ReportBuilder $builder): string
    {
        $performance = $builder->agentPerformance(now()->subDays(30));
        $avgMinutes = $performance->avg('avg_resolution_minutes') ?? 0;
        
        if ($avgMinutes == 0) return '0h';
        
        $hours = floor($avgMinutes / 60);
        $mins  = $avgMinutes % 60;
        
        return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
    }

    public function render()
    {
        return view('livewire.admin.admin-dashboard')
            ->layout('layouts.admin');
    }
}
