

<?php $__env->startSection('title', 'Submit a Ticket'); ?>

<?php $__env->startSection('content'); ?>
    <a href="<?php echo e(route('portal.index')); ?>" class="mb-4 inline-block text-sm text-blue-600 hover:underline">← Portal</a>

    <h1 class="mb-6 text-xl font-bold">Submit a Support Ticket</h1>

    <form method="POST" action="<?php echo e(route('portal.tickets.store')); ?>" class="space-y-4 rounded border border-gray-200 bg-white p-6">
        <?php echo csrf_field(); ?>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
            <input name="subject" type="text" value="<?php echo e(old('subject')); ?>" required
                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="5"
                      class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('description')); ?></textarea>
        </div>

        <div class="flex gap-4">
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-gray-700">Priority</label>
                <select name="priority" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="low" <?php if(old('priority') === 'low'): echo 'selected'; endif; ?>>Low</option>
                    <option value="medium" <?php if(old('priority', 'medium') === 'medium'): echo 'selected'; endif; ?>>Medium</option>
                    <option value="high" <?php if(old('priority') === 'high'): echo 'selected'; endif; ?>>High</option>
                    <option value="critical" <?php if(old('priority') === 'critical'): echo 'selected'; endif; ?>>Critical</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="incident" <?php if(old('type', 'incident') === 'incident'): echo 'selected'; endif; ?>>Incident</option>
                    <option value="service_request" <?php if(old('type') === 'service_request'): echo 'selected'; endif; ?>>Service Request</option>
                    <option value="problem" <?php if(old('type') === 'problem'): echo 'selected'; endif; ?>>Problem</option>
                    <option value="change" <?php if(old('type') === 'change'): echo 'selected'; endif; ?>>Change</option>
                </select>
            </div>
        </div>

        <button type="submit"
                class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Submit Ticket
        </button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/portal/tickets/create.blade.php ENDPATH**/ ?>