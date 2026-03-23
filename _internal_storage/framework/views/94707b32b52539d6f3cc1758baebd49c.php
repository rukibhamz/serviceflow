
<?php $currentStep = 1; ?>

<?php $__env->startSection('content'); ?>
<h2 class="text-xl font-semibold text-gray-800 mb-4">Environment Check</h2>
<p class="text-gray-500 mb-6 text-sm">Verifying your server meets the requirements to run ServiceFlow.</p>

<table class="w-full text-sm">
    <thead>
        <tr class="text-left text-gray-500 border-b">
            <th class="pb-2">Check</th>
            <th class="pb-2">Status</th>
            <th class="pb-2">Message</th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="border-b last:border-0">
            <td class="py-2 font-medium text-gray-700"><?php echo e($result['name']); ?></td>
            <td class="py-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($result['status'] === 'pass'): ?>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700">Pass</span>
                <?php elseif($result['status'] === 'warn'): ?>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-700">Warn</span>
                <?php else: ?>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">Fail</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
            <td class="py-2 text-gray-500"><?php echo e($result['message']); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </tbody>
</table>

<div class="mt-6 flex justify-end">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allPassed): ?>
        <a href="<?php echo e(route('installer.database')); ?>"
           class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
            Continue →
        </a>
    <?php else: ?>
        <p class="text-red-600 text-sm mr-4 self-center">Fix the failing checks before continuing.</p>
        <a href="<?php echo e(route('installer.index')); ?>"
           class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">
            Re-check
        </a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('installer.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/installer/index.blade.php ENDPATH**/ ?>