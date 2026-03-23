

<?php $__env->startSection('content'); ?>
    <div class="mb-4">
        <a href="<?php echo e(route('knowledge.show', $article->slug)); ?>" class="text-sm text-blue-600 hover:underline">&larr; Back to Article</a>
    </div>

    <h1 class="mb-6 text-xl font-semibold text-gray-900">Edit Article</h1>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('knowledge.article-editor', ['article' => $article]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1804001605-0', $__key);

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

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\agent\knowledge\edit.blade.php ENDPATH**/ ?>