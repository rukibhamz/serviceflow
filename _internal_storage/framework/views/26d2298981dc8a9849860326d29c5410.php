

<?php $__env->startSection('title', $item['name']); ?>

<?php $__env->startSection('content'); ?>
    <a href="<?php echo e(route('portal.catalogue.index')); ?>" class="mb-4 inline-block text-sm text-blue-600 hover:underline">← Service Catalogue</a>

    <h1 class="mb-1 text-xl font-bold"><?php echo e($item['name']); ?></h1>
    <p class="mb-6 text-sm text-gray-500"><?php echo e($item['description']); ?></p>

    <form method="POST" action="<?php echo e(route('portal.catalogue.submit', $item['id'])); ?>"
          class="space-y-4 rounded border border-gray-200 bg-white p-6">
        <?php echo csrf_field(); ?>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
            <input name="subject" type="text" value="<?php echo e(old('subject', $item['name'])); ?>" required
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
            <textarea name="description" rows="3"
                      class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('description')); ?></textarea>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    <?php echo e($field['label']); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field['required']): ?> <span class="text-red-500">*</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </label>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field['type'] === 'textarea'): ?>
                    <textarea name="<?php echo e($field['name']); ?>" rows="3" <?php if($field['required']): ?> required <?php endif; ?>
                              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old($field['name'])); ?></textarea>
                <?php elseif($field['type'] === 'select'): ?>
                    <select name="<?php echo e($field['name']); ?>" <?php if($field['required']): ?> required <?php endif; ?>
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                        <option value="">— Select —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $field['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option); ?>" <?php if(old($field['name']) === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                <?php elseif($field['type'] === 'checkbox'): ?>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="<?php echo e($field['name']); ?>" value="1"
                               <?php if(old($field['name'])): echo 'checked'; endif; ?> class="rounded border-gray-300" />
                        <?php echo e($field['label']); ?>

                    </label>
                <?php else: ?>
                    <input type="text" name="<?php echo e($field['name']); ?>" value="<?php echo e(old($field['name'])); ?>"
                           <?php if($field['required']): ?> required <?php endif; ?>
                           class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <button type="submit"
                class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Submit Request
        </button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/portal/catalogue/show.blade.php ENDPATH**/ ?>