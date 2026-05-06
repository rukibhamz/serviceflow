<div x-data class="pad-ds">
    <div class="mb-6">
        <div class="page-title text-xl font-semibold">Admin Overview</div>
        <div class="page-sub">Global system health and team performance — <?php echo e(now()->format('D d M Y')); ?></div>
    </div>

    
    <div class="stats-ds">
        <div class="stat-card">
            <div class="stat-label">Total Open Tickets</div>
            <div class="stat-val text-brand"><?php echo e($globalStats['total_open'] ?? 0); ?></div>
            <div class="stat-delta text-gray-400">System-wide total</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Global SLA Compliance</div>
            <div class="stat-val text-success"><?php echo e($slaCompliance['compliance_rate'] ?? 0); ?>%</div>
            <div class="stat-delta up">Target: 95%</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Critical Breaches</div>
            <div class="stat-val text-danger"><?php echo e($globalStats['breached'] ?? 0); ?></div>
            <div class="stat-delta down">Action required</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Global MTTR</div>
            <div class="stat-val"><?php echo e($globalStats['avg_mttr'] ?? '—'); ?></div>
            <div class="stat-delta text-gray-500">Mean time to resolve</div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        
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
                                data: [<?php echo e($slaCompliance['compliant'] ?? 0); ?>, <?php echo e($slaCompliance['breached'] ?? 0); ?>],
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

        
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Volume by Ticket Type (Last 30 Days)</div>
            </div>
            <div class="card-body h-[240px]">
                 <canvas x-init="
                    new Chart($el, {
                        type: 'bar',
                        data: {
                            labels: <?php echo e(Js::from($teamVolume['labels'] ?? [])); ?>,
                            datasets: [{
                                data: <?php echo e(Js::from($teamVolume['data'] ?? [])); ?>,
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
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/admin/admin-dashboard.blade.php ENDPATH**/ ?>