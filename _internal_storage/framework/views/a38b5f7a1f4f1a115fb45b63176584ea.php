

<?php $__env->startSection('title', $ticket->subject); ?>

<?php $__env->startSection('content'); ?>
    <a href="<?php echo e(route('portal.tickets.index')); ?>" class="mb-4 inline-block text-sm text-blue-600 hover:underline">← My Tickets</a>

    <div class="rounded border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-start justify-between">
            <h1 class="text-xl font-bold text-gray-900"><?php echo e($ticket->subject); ?></h1>
            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700"><?php echo e($ticket->status); ?></span>
        </div>

        <div class="mb-4 flex gap-4 text-xs text-gray-500">
            <span>Priority: <strong><?php echo e($ticket->priority); ?></strong></span>
            <span>Type: <strong><?php echo e($ticket->type); ?></strong></span>
            <span>Submitted: <strong><?php echo e($ticket->created_at->format('M j, Y')); ?></strong></span>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->description): ?>
            <div class="mb-6 rounded bg-gray-50 p-4 text-sm text-gray-700">
                <?php echo e($ticket->description); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <h2 class="mb-3 text-sm font-semibold text-gray-700">Updates</h2>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ticket->comments()->where('is_internal', false)->latest()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="mb-3 rounded border border-gray-100 bg-gray-50 p-3 text-sm">
                <p class="mb-1 text-xs text-gray-400"><?php echo e($comment->created_at->format('M j, Y H:i')); ?></p>
                <p class="text-gray-800"><?php echo e($comment->body); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-gray-400">No updates yet.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/portal/tickets/show.blade.php ENDPATH**/ ?>