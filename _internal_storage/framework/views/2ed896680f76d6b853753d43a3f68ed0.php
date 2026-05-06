<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">Problem Management</div>
            <div class="page-sub">Identify recurring incidents and manage root cause analysis</div>
        </div>
        <a href="<?php echo e(route('admin.tickets.create')); ?>?type=problem" class="btn-ds primary">+ Add New Problem</a>
    </div>

    <div class="stats-ds mb-2">
        <div class="stat-card"><div class="stat-label">Total Problems</div><div class="stat-val text-brand"><?php echo e($stats['total']); ?></div></div>
        <div class="stat-card"><div class="stat-label">Open</div><div class="stat-val text-red-500"><?php echo e($stats['open']); ?></div></div>
        <div class="stat-card"><div class="stat-label">Known Errors (KEDB)</div><div class="stat-val text-orange-500"><?php echo e($stats['known_errors']); ?></div></div>
    </div>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('problem.problem-list', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-273463364-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/admin/problem-manager.blade.php ENDPATH**/ ?>