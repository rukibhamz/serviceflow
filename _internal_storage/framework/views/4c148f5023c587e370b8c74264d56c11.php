<?php $__env->startSection('content'); ?>
    <?php
        $backRoute = match($ticket->type) {
            'problem' => 'admin.problems.index',
            'change' => 'admin.changes.index',
            default => 'admin.tickets.index',
        };
        $backLabel = match($ticket->type) {
            'problem' => 'Back to problems',
            'change' => 'Back to change requests',
            default => 'Back to tickets',
        };
    ?>
    <div class="mb-4">
        <a href="<?php echo e(route($backRoute)); ?>" class="text-sm text-blue-600 hover:underline">&larr; <?php echo e($backLabel); ?></a>
    </div>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tickets.ticket-resource', ['ticket' => $ticket]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2766983654-0', $__key);

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


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/admin/tickets/show.blade.php ENDPATH**/ ?>