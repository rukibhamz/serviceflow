

<?php $__env->startSection('content'); ?>
    <div class="mb-4">
        <a href="<?php echo e(route('agent.tickets.index')); ?>" class="text-sm text-blue-600 hover:underline">&larr; Back to tickets</a>
    </div>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tickets.ticket-resource', ['ticket' => $ticket]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-475298884-0', $__key);

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

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\agent\tickets\show.blade.php ENDPATH**/ ?>