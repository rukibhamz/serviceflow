<div class="pad-ds space-y-5">

    
    <div class="stats-ds">
        <div class="stat-card">
            <div class="stat-label">Open tickets</div>
            <div class="stat-val" style="color:var(--brand)"><?php echo e($counters['open'] ?? 0); ?></div>
            <div class="stat-delta text-gray-400">System-wide</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Resolved today</div>
            <div class="stat-val" style="color:var(--success)"><?php echo e($counters['resolved_today'] ?? 0); ?></div>
            <div class="stat-delta up">Keep it up</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">SLA breached</div>
            <div class="stat-val" style="color:var(--danger)"><?php echo e($counters['breached'] ?? 0); ?></div>
            <div class="stat-delta down">Action required</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg MTTR (me)</div>
            <div class="stat-val"><?php echo e($mttr); ?></div>
            <div class="stat-delta text-gray-400">Last 30 days</div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        
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
                                labels: <?php echo e(Js::from($volumeChart['labels'] ?? [])); ?>,
                                datasets: [{
                                    data: <?php echo e(Js::from($volumeChart['data'] ?? [])); ?>,
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

        
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Tickets by type</div>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($typeChart['data']) && array_sum($typeChart['data']) > 0): ?>
                <div style="height:160px; position:relative; margin-bottom:12px;">
                    <canvas id="typeChart"
                        x-data
                        x-init="
                            new Chart($el, {
                                type: 'pie',
                                data: {
                                    labels: <?php echo e(Js::from($typeChart['labels'] ?? [])); ?>,
                                    datasets: [{
                                        data: <?php echo e(Js::from($typeChart['data'] ?? [])); ?>,
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
                    <?php $colors = ['#1a4fa0','#f97316','#e53935','#22c55e','#a78bfa','#38bdf8']; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $typeChart['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:<?php echo e($colors[$i % count($colors)]); ?>"></div>
                            <span class="text-gray-600"><?php echo e($label); ?></span>
                        </div>
                        <span class="font-medium text-gray-700"><?php echo e($typeChart['pcts'][$i] ?? ''); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php else: ?>
                <div class="text-xs text-gray-400 text-center py-8">No ticket data yet</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        
        <div class="card-ds lg:col-span-2">
            <div class="card-hdr">
                <div class="card-title">SLA performance by team</div>
            </div>
            <div class="card-body p-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($teamPerformance)): ?>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $teamPerformance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="font-medium"><?php echo e($team['name']); ?></td>
                            <td><?php echo e($team['open']); ?></td>
                            <td><?php echo e($team['resolved']); ?></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="sla-track flex-1">
                                        <div class="sla-fill <?php echo e($team['status'] === 'on_track' ? 'ok' : ($team['status'] === 'at_risk' ? 'warn' : 'breach')); ?>"
                                             style="width:<?php echo e($team['sla_pct']); ?>%"></div>
                                    </div>
                                    <span class="text-xs font-medium w-8 text-right"><?php echo e($team['sla_pct']); ?>%</span>
                                </div>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($team['status'] === 'on_track'): ?>
                                    <span class="badge-ds closed">On track</span>
                                <?php elseif($team['status'] === 'at_risk'): ?>
                                    <span class="badge-ds pending">At risk</span>
                                <?php else: ?>
                                    <span class="badge-ds critical">Breached</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="text-xs text-gray-400 text-center py-8">No teams configured yet</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Recent activity</div>
            </div>
            <div class="card-body p-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($recentActivity)): ?>
                <div class="divide-y divide-gray-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $dot = match($act['type'] ?? 'default') {
                            'tickets'  => '#22c55e',
                            'sla'      => '#ef4444',
                            'changes'  => '#1a4fa0',
                            'knowledge'=> '#38bdf8',
                            default    => '#9ca3af',
                        };
                    ?>
                    <div class="flex items-start gap-3 px-4 py-3">
                        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" style="background:<?php echo e($dot); ?>"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 leading-snug">
                                <span class="font-medium"><?php echo e($act['causer']); ?></span>
                                <?php echo e($act['description']); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($act['subject']): ?>
                                    <span class="text-gray-400">· <?php echo e(Str::limit($act['subject'], 20)); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-0.5"><?php echo e($act['time']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php else: ?>
                <div class="text-xs text-gray-400 text-center py-8">No recent activity</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($csatChart['average'] ?? 0) > 0): ?>
                <div class="border-t border-gray-100 px-4 py-3">
                    <div class="text-xs font-medium text-gray-500 mb-2">CSAT this week</div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold" style="color:var(--success)"><?php echo e($csatChart['average']); ?></span>
                        <span class="text-xs text-gray-400">/5</span>
                    </div>
                    <div class="mt-2 flex gap-0.5 h-1.5">
                        <?php $csatColors = ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e']; $csatTotal = max(array_sum($csatChart['data'] ?? [1]), 1); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $csatChart['data'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ci => $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-full h-full" style="width:<?php echo e(round($cv/$csatTotal*100)); ?>%; background:<?php echo e($csatColors[$ci] ?? '#ccc'); ?>"></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/dashboard/dashboard-widgets.blade.php ENDPATH**/ ?>