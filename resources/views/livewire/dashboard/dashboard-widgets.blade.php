<div class="pad-ds space-y-5">

    {{-- ── Stat cards ── --}}
    <div class="stats-ds">
        <div class="stat-card">
            <div class="stat-label">Open tickets</div>
            <div class="stat-val" style="color:var(--brand)">{{ $counters['open'] ?? 0 }}</div>
            <div class="stat-delta text-gray-400">System-wide</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Resolved today</div>
            <div class="stat-val" style="color:var(--success)">{{ $counters['resolved_today'] ?? 0 }}</div>
            <div class="stat-delta up">Keep it up</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">SLA breached</div>
            <div class="stat-val" style="color:var(--danger)">{{ $counters['breached'] ?? 0 }}</div>
            <div class="stat-delta down">Action required</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg MTTR (me)</div>
            <div class="stat-val">{{ $mttr }}</div>
            <div class="stat-delta text-gray-400">Last 30 days</div>
        </div>
    </div>

    {{-- ── Row 2: Bar chart + Pie chart ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Ticket volume bar chart (last 7 days) --}}
        <div class="card-ds lg:col-span-2">
            <div class="card-hdr">
                <div class="card-title">Ticket volume — last 7 days</div>
                <span class="text-xs text-gray-400">Daily</span>
            </div>
            <div class="card-body" style="height:220px; position:relative;">
                <canvas id="volumeChart"
                    x-data
                    x-init="
                        new Chart($el, {
                            type: 'bar',
                            data: {
                                labels: {{ Js::from($volumeChart['labels'] ?? []) }},
                                datasets: [{
                                    data: {{ Js::from($volumeChart['data'] ?? []) }},
                                    backgroundColor: 'rgba(26,79,160,0.55)',
                                    hoverBackgroundColor: 'rgba(26,79,160,0.85)',
                                    borderRadius: 5,
                                    borderSkipped: false
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.y + ' tickets' } } },
                                scales: {
                                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f3f4f6' } },
                                    x: { ticks: { font: { size: 11 } }, grid: { display: false } }
                                }
                            }
                        })
                    ">
                </canvas>
            </div>
        </div>

        {{-- Tickets by type pie chart --}}
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Tickets by type</div>
            </div>
            <div class="card-body">
                @if(!empty($typeChart['data']) && array_sum($typeChart['data']) > 0)
                <div style="height:160px; position:relative; margin-bottom:12px;">
                    <canvas id="typeChart"
                        x-data
                        x-init="
                            new Chart($el, {
                                type: 'pie',
                                data: {
                                    labels: {{ Js::from($typeChart['labels'] ?? []) }},
                                    datasets: [{
                                        data: {{ Js::from($typeChart['data'] ?? []) }},
                                        backgroundColor: ['#1a4fa0','#f97316','#e53935','#22c55e','#a78bfa','#38bdf8'],
                                        borderWidth: 2,
                                        borderColor: '#fff'
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
                <div class="space-y-1.5">
                    @php $colors = ['#1a4fa0','#f97316','#e53935','#22c55e','#a78bfa','#38bdf8']; @endphp
                    @foreach($typeChart['labels'] as $i => $label)
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $colors[$i % count($colors)] }}"></div>
                            <span class="text-gray-600">{{ $label }}</span>
                        </div>
                        <span class="font-medium text-gray-700">{{ $typeChart['pcts'][$i] ?? '' }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-xs text-gray-400 text-center py-8">No ticket data yet</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Row 3: Team performance + Recent activity ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- SLA performance by team --}}
        <div class="card-ds lg:col-span-2">
            <div class="card-hdr">
                <div class="card-title">SLA performance by team</div>
            </div>
            <div class="card-body p-0">
                @if(!empty($teamPerformance))
                <table class="table-ds">
                    <thead>
                        <tr>
                            <th>Team</th>
                            <th>Open</th>
                            <th>Resolved</th>
                            <th>SLA %</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teamPerformance as $team)
                        <tr>
                            <td class="font-medium">{{ $team['name'] }}</td>
                            <td>{{ $team['open'] }}</td>
                            <td>{{ $team['resolved'] }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="sla-track flex-1">
                                        <div class="sla-fill {{ $team['status'] === 'on_track' ? 'ok' : ($team['status'] === 'at_risk' ? 'warn' : 'breach') }}"
                                             style="width:{{ $team['sla_pct'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium w-8 text-right">{{ $team['sla_pct'] }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($team['status'] === 'on_track')
                                    <span class="badge-ds closed">On track</span>
                                @elseif($team['status'] === 'at_risk')
                                    <span class="badge-ds pending">At risk</span>
                                @else
                                    <span class="badge-ds critical">Breached</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-xs text-gray-400 text-center py-8">No teams configured yet</div>
                @endif
            </div>
        </div>

        {{-- Recent activity feed --}}
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Recent activity</div>
            </div>
            <div class="card-body p-0">
                @if(!empty($recentActivity))
                <div class="divide-y divide-gray-50">
                    @foreach($recentActivity as $act)
                    @php
                        $dot = match($act['type'] ?? 'default') {
                            'tickets'  => '#22c55e',
                            'sla'      => '#ef4444',
                            'changes'  => '#1a4fa0',
                            'knowledge'=> '#38bdf8',
                            default    => '#9ca3af',
                        };
                    @endphp
                    <div class="flex items-start gap-3 px-4 py-3">
                        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" style="background:{{ $dot }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 leading-snug">
                                <span class="font-medium">{{ $act['causer'] }}</span>
                                {{ $act['description'] }}
                                @if($act['subject'])
                                    <span class="text-gray-400">· {{ Str::limit($act['subject'], 20) }}</span>
                                @endif
                            </p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $act['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-xs text-gray-400 text-center py-8">No recent activity</div>
                @endif

                {{-- CSAT this week --}}
                @if(($csatChart['average'] ?? 0) > 0)
                <div class="border-t border-gray-100 px-4 py-3">
                    <div class="text-xs font-medium text-gray-500 mb-2">CSAT this week</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold" style="color:var(--success)">{{ $csatChart['average'] }}</span>
                        <span class="text-xs text-gray-400">/5</span>
                    </div>
                    <div class="mt-2 flex gap-0.5 h-1.5">
                        @php $csatColors = ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e']; $csatTotal = max(array_sum($csatChart['data'] ?? [1]), 1); @endphp
                        @foreach($csatChart['data'] ?? [] as $ci => $cv)
                        <div class="rounded-full h-full" style="width:{{ round($cv/$csatTotal*100) }}%; background:{{ $csatColors[$ci] ?? '#ccc' }}"></div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
