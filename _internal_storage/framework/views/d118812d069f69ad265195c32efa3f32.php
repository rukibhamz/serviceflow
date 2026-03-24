

<?php $__env->startSection('title', 'Service Catalogue'); ?>

<?php $__env->startSection('content'); ?>
    <a href="<?php echo e(route('portal.index')); ?>" class="mb-4 inline-block text-sm text-blue-600 hover:underline">← Portal</a>

    <h1 class="mb-6 text-xl font-bold">Service Catalogue</h1>

    <div class="grid gap-4 sm:grid-cols-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('portal.catalogue.show', $item['id'])); ?>"
               class="block rounded border border-gray-200 bg-white p-5 hover:shadow-md transition-shadow">
                <h2 class="mb-1 font-semibold text-gray-900"><?php echo e($item['name']); ?></h2>
                <p class="mb-3 text-sm text-gray-500"><?php echo e($item['description']); ?></p>
                <div class="flex gap-2 text-xs">
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-600"><?php echo e($item['type']); ?></span>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-blue-700"><?php echo e($item['priority']); ?></span>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/portal/catalogue/index.blade.php ENDPATH**/ ?>