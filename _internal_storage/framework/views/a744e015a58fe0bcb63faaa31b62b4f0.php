

<?php $__env->startSection('title', 'Thank You'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-lg mx-auto py-16 text-center">
    <div class="text-5xl mb-4">🎉</div>
    <h1 class="text-2xl font-semibold text-gray-800 mb-2">Thank you for your feedback!</h1>
    <p class="text-gray-500">Your response has been recorded. We appreciate you taking the time to rate our support.</p>
    <a href="<?php echo e(route('portal.index')); ?>" class="mt-6 inline-block text-indigo-600 hover:underline">Back to portal</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\portal\csat\thankyou.blade.php ENDPATH**/ ?>