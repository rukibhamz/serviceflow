

<?php $__env->startSection('page-header'); ?>
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">Problem Management</div>
            <div class="page-sub">Identify recurring incidents and manage root cause analysis</div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $total      = \App\Models\Ticket::where('type','problem')->count();
    $open       = \App\Models\Ticket::where('type','problem')->whereNotIn('status',['resolved','closed'])->count();
    $knownErrors= \App\Models\Ticket::where('type','problem')->whereJsonContains('custom_fields->known_error', true)->count();
?>
<div class="stats-ds mb-4">
    <div class="stat-card"><div class="stat-label">Total Problems</div><div class="stat-val text-brand"><?php echo e($total); ?></div></div>
    <div class="stat-card"><div class="stat-label">Open</div><div class="stat-val text-red-500"><?php echo e($open); ?></div></div>
    <div class="stat-card"><div class="stat-label">Known Errors (KEDB)</div><div class="stat-val text-orange-500"><?php echo e($knownErrors); ?></div></div>
</div>
<?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('problem.problem-list', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-103556168-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/admin/itsm/problems.blade.php ENDPATH**/ ?>