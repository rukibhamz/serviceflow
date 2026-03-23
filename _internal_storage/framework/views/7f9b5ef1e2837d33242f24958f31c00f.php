

<?php $__env->startSection('title', 'Rate Your Support Experience'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-lg mx-auto py-12">
    <h1 class="text-2xl font-semibold text-gray-800 mb-2">How did we do?</h1>
    <p class="text-gray-500 mb-6">Please rate your experience for ticket #<?php echo e($survey->ticket->subject ?? $survey->ticket_id); ?>.</p>

    <form method="POST" action="<?php echo e(route('portal.csat.feedback.store', $survey->token)); ?>" class="space-y-5">
        <?php echo csrf_field(); ?>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
            <div class="flex gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="<?php echo e($i); ?>"
                               <?php echo e(old('rating', $survey->rating) == $i ? 'checked' : ''); ?>

                               class="sr-only peer" required>
                        <span class="text-3xl peer-checked:scale-110 transition-transform select-none
                                     <?php echo e(old('rating', $survey->rating) >= $i ? 'text-yellow-400' : 'text-gray-300'); ?>">★</span>
                    </label>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Comments <span class="text-gray-400">(optional)</span></label>
            <textarea id="comment" name="comment" rows="4"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                      maxlength="1000" placeholder="Tell us more…"><?php echo e(old('comment', $survey->comment)); ?></textarea>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition">
            Submit Feedback
        </button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\portal\csat\feedback.blade.php ENDPATH**/ ?>