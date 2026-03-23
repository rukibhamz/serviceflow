<div x-data class="space-y-6">

    {{-- Counter cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Open Tickets</p>
            <p class="text-3xl font-bold text-gray-800">{{ $counters['open'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Unassigned</p>
            <p class="text-3xl font-bold text-orange-500">{{ $counters['unassigned'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">SLA Breaches</p>
            <p class="text-3xl font-bold text-red-500">{{ $counters['breached'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Resolved Today</p>
            <p class="text-3xl font-bold text-green-600">{{ $counters['resolved_today'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Ticket volume by status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Ticket Volume by Status (last 30 days)</h3>
            <canvas id="volumeChart" height="200"
                    x-init="
                        new Chart($el, {
                            type: 'bar',
                            data: {
                                labels: {{ Js::from($volumeChart['labels'] ?? []) }},
                                datasets: [{
                                    label: 'Tickets',
                                    data: {{ Js::from($volumeChart['data'] ?? []) }},
                                    backgroundColor: ['#6366f1','#f59e0b','#10b981','#ef4444','#8b5cf6'],
                                }]
                            },
                            options: { responsive: true, plugins: { legend: { display: false } } }
                        })
                    ">
            </canvas>
        </div>

        {{-- CSAT distribution --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">CSAT Scores (last 30 days)</h3>
            <p class="text-xs text-gray-400 mb-3">Average: <span class="font-semibold text-gray-700">{{ $csatChart['average'] ?? '—' }}</span> / 5</p>
            <canvas id="csatChart" height="200"
                    x-init="
                        new Chart($el, {
                            type: 'doughnut',
                            data: {
                                labels: {{ Js::from($csatChart['labels'] ?? []) }},
                                datasets: [{
                                    data: {{ Js::from($csatChart['data'] ?? []) }},
                                    backgroundColor: ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e'],
                                }]
                            },
                            options: { responsive: true }
                        })
                    ">
            </canvas>
        </div>
    </div>
</div>
