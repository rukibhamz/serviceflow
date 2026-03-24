

<?php $__env->startSection('title', 'My Tickets'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold">My Tickets</h1>
        <a href="<?php echo e(route('portal.tickets.create')); ?>"
           class="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
            New Ticket
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <a href="<?php echo e(route('portal.tickets.show', $ticket->ulid)); ?>"
           class="mb-2 flex items-center justify-between rounded border border-gray-200 bg-white px-4 py-3 hover:shadow-sm">
            <div>
                <p class="font-medium text-gray-800"><?php echo e($ticket->subject); ?></p>
                <p class="text-xs text-gray-400"><?php echo e($ticket->created_at->format('M j, Y')); ?></p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600"><?php echo e($ticket->priority); ?></span>
                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700"><?php echo e($ticket->status); ?></span>
            </div>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-sm text-gray-500">You have no tickets yet.</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mt-4"><?php echo e($tickets->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/portal/tickets/index.blade.php ENDPATH**/ ?>