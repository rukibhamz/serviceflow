<div x-data class="pad-ds">

    {{-- Stats Row --}}
    <div class="stats-ds">
        <div class="stat-card">
            <div class="stat-label">My open tickets</div>
            <div class="stat-val text-brand">{{ $counters['open'] ?? 0 }}</div>
            <div class="stat-delta text-gray-400">Assigned to you</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Resolved today</div>
            <div class="stat-val text-success">{{ $counters['resolved_today'] ?? 0 }}</div>
            <div class="stat-delta up">Great job!</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">SLA breached</div>
            <div class="stat-val text-danger">{{ $counters['breached'] ?? 0 }}</div>
            <div class="stat-delta down">Action required</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">My avg. MTTR</div>
            <div class="stat-val">{{ $mttr }}</div>
            <div class="stat-delta text-gray-500">Last 30 days</div>
        </div>
    </div>

    {{-- Middle Section: 2-Col layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        
        {{-- Charts: Team Productivity --}}
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Team Productivity (Resolved Tickets)</div>
            </div>
            <div class="card-body">
                <canvas id="productivityChart" height="200"
                    x-init="
                        new Chart($el, {
                            type: 'bar',
                            data: {
                                labels: {{ Js::from($productivityData['labels'] ?? []) }},
                                datasets: [{
                                    label: 'Resolved',
                                    data: {{ Js::from($productivityData['data'] ?? []) }},
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
                    ">
                </canvas>
            </div>
        </div>

        {{-- CSAT distribution --}}
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">CSAT & Satisfaction</div>
            </div>
            <div class="card-body flex items-center justify-between">
                <div class="w-1/2">
                    <canvas id="csatChart" height="180"
                        x-init="
                            new Chart($el, {
                                type: 'doughnut',
                                data: {
                                    labels: {{ Js::from($csatChart['labels'] ?? []) }},
                                    datasets: [{
                                        data: {{ Js::from($csatChart['data'] ?? []) }},
                                        backgroundColor: ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e'],
                                        borderWidth: 0,
                                        cutout: '70%'
                                    }]
                                },
                                options: { 
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } }
                                }
                            })
                        ">
                    </canvas>
                </div>
                <div class="w-1/2 pl-6">
                    <div class="text-3xl font-bold text-success">{{ $csatChart['average'] ?? '—' }}<span class="text-sm font-normal text-gray-400">/5</span></div>
                    <div class="text-xs text-gray-500 mt-1">Satisfaction index</div>
                    <div class="mt-4 space-y-1">
                        @foreach($csatChart['labels'] as $index => $label)
                            <div class="flex items-center gap-2 text-xs">
                                <div class="w-2 h-2 rounded-full" style="background-color: {{ ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e'][$index] ?? '#ccc' }}"></div>
                                <span class="text-gray-600">{{ $label }}:</span>
                                <span class="font-semibold">{{ $csatChart['data'][$index] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
