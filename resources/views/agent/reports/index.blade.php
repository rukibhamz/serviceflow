@extends('layouts.agent')

@section('page-header')
    <div class="page-title">Reports</div>
    <div class="page-sub">Analyse team performance, SLA compliance, and ticket trends</div>
@endsection

@section('content')
@php
    use App\Models\Ticket;
    use App\Models\User;
    use Carbon\Carbon;

    $now        = Carbon::now();
    $startMonth = $now->copy()->startOfMonth();
    $startWeek  = $now->copy()->startOfWeek();

    // Ticket volume by status
    $byStatus = Ticket::selectRaw('status, count(*) as total')
        ->groupBy('status')->pluck('total', 'status');

    // Ticket volume by priority
    $byPriority = Ticket::selectRaw('priority, count(*) as total')
        ->groupBy('priority')->pluck('total', 'priority');

    // Created this month vs last month
    $thisMonth = Ticket::where('created_at', '>=', $startMonth)->count();
    $lastMonth = Ticket::whereBetween('created_at', [
        $now->copy()->subMonth()->startOfMonth(),
        $now->copy()->subMonth()->endOfMonth(),
    ])->count();

    // Resolved this month
    $resolvedMonth = Ticket::whereIn('status', ['resolved','closed'])
        ->where('updated_at', '>=', $startMonth)->count();

    // Avg resolution time (hours) for resolved tickets this month
    $avgResolution = Ticket::whereIn('status', ['resolved','closed'])
        ->where('updated_at', '>=', $startMonth)
        ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
        ->value('avg_hours');

    // SLA breached this month — check sla_timers table
    $slaBreached = \App\Models\SlaTimer::where('breached', true)
        ->where('created_at', '>=', $startMonth)->count();

    // Top agents by resolved tickets this month
    $topAgents = Ticket::whereIn('status', ['resolved','closed'])
        ->where('updated_at', '>=', $startMonth)
        ->whereNotNull('assignee_id')
        ->selectRaw('assignee_id, count(*) as resolved')
        ->groupBy('assignee_id')
        ->orderByDesc('resolved')
        ->with('assignee')
        ->limit(5)
        ->get();

    // Daily ticket volume last 14 days
    $dailyVolume = Ticket::where('created_at', '>=', $now->copy()->subDays(13)->startOfDay())
        ->selectRaw('DATE(created_at) as date, count(*) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->pluck('total', 'date');

    $days = collect();
    for ($i = 13; $i >= 0; $i--) {
        $d = $now->copy()->subDays($i)->format('Y-m-d');
        $days[$d] = $dailyVolume[$d] ?? 0;
    }
@endphp

{{-- KPI cards --}}
<div class="stats-ds mb-6">
    <div class="stat-card">
        <div class="stat-label">Created This Month</div>
        <div class="stat-val text-brand">{{ $thisMonth }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $lastMonth }} last month</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Resolved This Month</div>
        <div class="stat-val text-green-600">{{ $resolvedMonth }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Avg Resolution Time</div>
        <div class="stat-val text-blue-600">{{ $avgResolution ? round($avgResolution) . 'h' : '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">SLA Breaches</div>
        <div class="stat-val {{ $slaBreached > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ $slaBreached }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Daily volume chart --}}
    <div class="card-ds lg:col-span-2">
        <div class="card-hdr">
            <div class="card-title">Ticket Volume — Last 14 Days</div>
        </div>
        <div class="card-body">
            <canvas id="volumeChart" height="100"></canvas>
        </div>
    </div>

    {{-- By status --}}
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">By Status</div></div>
        <div class="card-body space-y-2">
            @foreach (['open','in_progress','pending','resolved','closed'] as $s)
            @php $count = $byStatus[$s] ?? 0; $total = $byStatus->sum() ?: 1; @endphp
            <div>
                <div class="flex justify-between text-xs text-gray-600 mb-0.5">
                    <span>{{ ucfirst(str_replace('_',' ',$s)) }}</span>
                    <span class="font-medium">{{ $count }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-brand" style="width:{{ round($count/$total*100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- By priority --}}
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">By Priority</div></div>
        <div class="card-body">
            <canvas id="priorityChart" height="160"></canvas>
        </div>
    </div>

    {{-- Top agents --}}
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">Top Agents This Month</div></div>
        <div class="card-body">
            @forelse ($topAgents as $row)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-accent flex items-center justify-center text-white text-xs font-semibold">
                        {{ strtoupper(substr($row->assignee?->name ?? 'U', 0, 2)) }}
                    </div>
                    <span class="text-sm text-gray-700">{{ $row->assignee?->name ?? 'Unknown' }}</span>
                </div>
                <span class="text-sm font-semibold text-green-600">{{ $row->resolved }} resolved</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">No resolved tickets this month.</p>
            @endforelse
        </div>
    </div>

</div>

@push('scripts')
<script>
(function() {
    // Volume chart
    new Chart(document.getElementById('volumeChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($days->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values()) !!},
            datasets: [{
                label: 'Tickets Created',
                data: {!! json_encode($days->values()) !!},
                backgroundColor: 'rgba(26,79,160,0.15)',
                borderColor: '#1a4fa0',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Priority doughnut
    new Chart(document.getElementById('priorityChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($byPriority->keys()->map(fn($p) => ucfirst($p))->values()) !!},
            datasets: [{
                data: {!! json_encode($byPriority->values()) !!},
                backgroundColor: ['#ef4444','#f97316','#eab308','#22c55e','#94a3b8'],
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            cutout: '65%',
        }
    });
})();
</script>
@endpush
@endsection
