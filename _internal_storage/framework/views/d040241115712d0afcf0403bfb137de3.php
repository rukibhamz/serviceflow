<?php $__env->startSection('content'); ?>
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Knowledge Base</h1>
        <a href="<?php echo e(route('agent.knowledge.create')); ?>"
           class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            New Article
        </a>
    </div>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('knowledge.article-list', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-4149067860-0', $__key);

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

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/agent/knowledge/index.blade.php ENDPATH**/ ?>