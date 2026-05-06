

<?php $__env->startSection('page-header'); ?>
    <div class="page-title">Reports</div>
    <div class="page-sub">Analyse team performance, SLA compliance, and ticket trends</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    use App\Models\Ticket;
    use Carbon\Carbon;

    $now        = Carbon::now();
    $startMonth = $now->copy()->startOfMonth();

    $byStatus   = Ticket::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
    $byPriority = Ticket::selectRaw('priority, count(*) as total')->groupBy('priority')->pluck('total', 'priority');
    $thisMonth  = Ticket::where('created_at', '>=', $startMonth)->count();
    $lastMonth  = Ticket::whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])->count();
    $resolvedMonth = Ticket::whereIn('status', ['resolved','closed'])->where('updated_at', '>=', $startMonth)->count();
    $avgResolution = Ticket::whereIn('status', ['resolved','closed'])->where('updated_at', '>=', $startMonth)->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')->value('avg_hours');
    $slaBreached = \App\Models\SlaTimer::where('breached', true)->where('created_at', '>=', $startMonth)->count();

    $topAgents = Ticket::whereIn('status', ['resolved','closed'])->where('updated_at', '>=', $startMonth)->whereNotNull('assignee_id')->selectRaw('assignee_id, count(*) as resolved')->groupBy('assignee_id')->orderByDesc('resolved')->with('assignee')->limit(5)->get();

    $dailyVolume = Ticket::where('created_at', '>=', $now->copy()->subDays(13)->startOfDay())->selectRaw('DATE(created_at) as date, count(*) as total')->groupBy('date')->orderBy('date')->pluck('total', 'date');
    $days = collect();
    for ($i = 13; $i >= 0; $i--) {
        $d = $now->copy()->subDays($i)->format('Y-m-d');
        $days[$d] = $dailyVolume[$d] ?? 0;
    }
?>

<div class="stats-ds mb-6">
    <div class="stat-card"><div class="stat-label">Created This Month</div><div class="stat-val text-brand"><?php echo e($thisMonth); ?></div><div class="text-xs text-gray-400 mt-1"><?php echo e($lastMonth); ?> last month</div></div>
    <div class="stat-card"><div class="stat-label">Resolved This Month</div><div class="stat-val text-green-600"><?php echo e($resolvedMonth); ?></div></div>
    <div class="stat-card"><div class="stat-label">Avg Resolution Time</div><div class="stat-val text-blue-600"><?php echo e($avgResolution ? round($avgResolution).'h' : '—'); ?></div></div>
    <div class="stat-card"><div class="stat-label">SLA Breaches</div><div class="stat-val <?php echo e($slaBreached > 0 ? 'text-red-500' : 'text-gray-400'); ?>"><?php echo e($slaBreached); ?></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <div class="card-ds lg:col-span-2">
        <div class="card-hdr"><div class="card-title">Ticket Volume — Last 14 Days</div></div>
        <div class="card-body"><canvas id="volumeChart" height="100"></canvas></div>
    </div>
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">By Status</div></div>
        <div class="card-body space-y-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['open','in_progress','pending','resolved','closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $count = $byStatus[$s] ?? 0; $total = $byStatus->sum() ?: 1; ?>
            <div>
                <div class="flex justify-between text-xs text-gray-600 mb-0.5"><span><?php echo e(ucfirst(str_replace('_',' ',$s))); ?></span><span class="font-medium"><?php echo e($count); ?></span></div>
                <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-brand" style="width:<?php echo e(round($count/$total*100)); ?>%"></div></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">By Priority</div></div>
        <div class="card-body"><canvas id="priorityChart" height="160"></canvas></div>
    </div>
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">Top Agents This Month</div></div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topAgents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-accent flex items-center justify-center text-white text-xs font-semibold"><?php echo e(strtoupper(substr($row->assignee?->name ?? 'U', 0, 2))); ?></div>
                    <span class="text-sm text-gray-700"><?php echo e($row->assignee?->name ?? 'Unknown'); ?></span>
                </div>
                <span class="text-sm font-semibold text-green-600"><?php echo e($row->resolved); ?> resolved</span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-gray-400 text-center py-4">No resolved tickets this month.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function() {
    new Chart(document.getElementById('volumeChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($days->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values()); ?>,
            datasets: [{ label: 'Tickets Created', data: <?php echo json_encode($days->values()); ?>, backgroundColor: 'rgba(26,79,160,0.15)', borderColor: '#1a4fa0', borderWidth: 2, borderRadius: 4 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
    new Chart(document.getElementById('priorityChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($byPriority->keys()->map(fn($p) => ucfirst($p))->values()); ?>,
            datasets: [{ data: <?php echo json_encode($byPriority->values()); ?>, backgroundColor: ['#ef4444','#f97316','#eab308','#22c55e','#94a3b8'], borderWidth: 2 }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
    });
})();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/admin/reports/index.blade.php ENDPATH**/ ?>