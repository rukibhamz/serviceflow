<div x-data class="pad-ds">

    
    <div class="stats-ds">
        <div class="stat-card">
            <div class="stat-label">My open tickets</div>
            <div class="stat-val text-brand"><?php echo e($counters['open'] ?? 0); ?></div>
            <div class="stat-delta text-gray-400">Assigned to you</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Resolved today</div>
            <div class="stat-val text-success"><?php echo e($counters['resolved_today'] ?? 0); ?></div>
            <div class="stat-delta up">Great job!</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">SLA breached</div>
            <div class="stat-val text-danger"><?php echo e($counters['breached'] ?? 0); ?></div>
            <div class="stat-delta down">Action required</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">My avg. MTTR</div>
            <div class="stat-val"><?php echo e($mttr); ?></div>
            <div class="stat-delta text-gray-500">Last 30 days</div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        
        
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
                                labels: <?php echo e(Js::from($productivityData['labels'] ?? [])); ?>,
                                datasets: [{
                                    label: 'Resolved',
                                    data: <?php echo e(Js::from($productivityData['data'] ?? [])); ?>,
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
                                    labels: <?php echo e(Js::from($csatChart['labels'] ?? [])); ?>,
                                    datasets: [{
                                        data: <?php echo e(Js::from($csatChart['data'] ?? [])); ?>,
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
                    <div class="text-3xl font-bold text-success"><?php echo e($csatChart['average'] ?? '—'); ?><span class="text-sm font-normal text-gray-400">/5</span></div>
                    <div class="text-xs text-gray-500 mt-1">Satisfaction index</div>
                    <div class="mt-4 space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $csatChart['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-2 text-xs">
                                <div class="w-2 h-2 rounded-full" style="background-color: <?php echo e(['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e'][$index] ?? '#ccc'); ?>"></div>
                                <span class="text-gray-600"><?php echo e($label); ?>:</span>
                                <span class="font-semibold"><?php echo e($csatChart['data'][$index]); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/dashboard/dashboard-widgets.blade.php ENDPATH**/ ?>