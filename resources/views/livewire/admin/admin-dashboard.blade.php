<div x-data class="pad-ds">
    <div class="mb-6">
        <div class="page-title text-xl font-semibold">Admin Overview</div>
        <div class="page-sub">Global system health and team performance — {{ now()->format('D d M Y') }}</div>
    </div>

    {{-- Global Stats Row --}}
    <div class="stats-ds">
        <div class="stat-card">
            <div class="stat-label">Total Open Tickets</div>
            <div class="stat-val text-brand">{{ $globalStats['total_open'] ?? 0 }}</div>
            <div class="stat-delta text-gray-400">System-wide total</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Global SLA Compliance</div>
            <div class="stat-val text-success">{{ $slaCompliance['compliance_rate'] ?? 0 }}%</div>
            <div class="stat-delta up">Target: 95%</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Critical Breaches</div>
            <div class="stat-val text-danger">{{ $globalStats['breached'] ?? 0 }}</div>
            <div class="stat-delta down">Action required</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Global MTTR</div>
            <div class="stat-val">{{ $globalStats['avg_mttr'] ?? '—' }}</div>
            <div class="stat-delta text-gray-500">Mean time to resolve</div>
        </div>
    </div>

    {{-- Secondary Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        {{-- SLA Breakdown card --}}
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">SLA Compliance Distribution</div>
            </div>
            <div class="card-body h-[240px]">
                <canvas x-init="
                    new Chart($el, {
                        type: 'pie',
                        data: {
                            labels: ['Compliant', 'Breached'],
                            datasets: [{
                                data: [{{ $slaCompliance['compliant'] ?? 0 }}, {{ $slaCompliance['breached'] ?? 0 }}],
                                backgroundColor: ['#22c55e', '#ef4444'],
                                borderWidth: 0
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } }
                        }
                    })
                "></canvas>
            </div>
        </div>

        {{-- Ticket Volume per Type --}}
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Volume by Ticket Type (Last 30 Days)</div>
            </div>
            <div class="card-body h-[240px]">
                 <canvas x-init="
                    new Chart($el, {
                        type: 'bar',
                        data: {
                            labels: {{ Js::from($teamVolume['labels'] ?? []) }},
                            datasets: [{
                                data: {{ Js::from($teamVolume['data'] ?? []) }},
                                backgroundColor: '#1a4fa0cc',
                                borderRadius: 4
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, grid: { display: false } },
                                x: { grid: { display: false } }
                            }
                        }
                    })
                "></canvas>
            </div>
        </div>
    </div>
</div>
