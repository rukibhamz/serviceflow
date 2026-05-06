<?php $currentStep = 4; ?>

<?php $__env->startSection('content'); ?>
<div class="text-center mb-6">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h2 class="text-2xl font-bold text-gray-800">Installation Complete!</h2>
    <p class="text-gray-500 mt-2 text-sm">ServiceFlow has been successfully installed.</p>
</div>


<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-2">cPanel Email Pipe Configuration (optional)</h3>
    <p class="text-xs text-gray-500 mb-3">
        To enable inbound email-to-ticket, add the following line to your cPanel email account's
        <code class="bg-gray-200 px-1 rounded">.forward</code> file:
    </p>
    <pre class="bg-gray-800 text-green-400 text-xs rounded p-3 overflow-x-auto"><?php echo e($forwardContent); ?></pre>

    <p class="text-xs text-gray-500 mt-3">Pipe script location:</p>
    <pre class="bg-gray-800 text-green-400 text-xs rounded p-3 overflow-x-auto mt-1"><?php echo e($pipeScriptPath); ?></pre>
</div>


<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Cron Job</h3>
    <p class="text-xs text-gray-500 mb-2">Add this cron job to run scheduled tasks (SLA checks, etc.):</p>
    <pre class="bg-gray-800 text-green-400 text-xs rounded p-3 overflow-x-auto">* * * * * php <?php echo e(base_path('artisan')); ?> schedule:run >> /dev/null 2>&1</pre>
</div>

<div class="text-center">
    <a href="<?php echo e(url('/')); ?>"
       class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700">
        Go to Login →
    </a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('installer.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/installer/finish.blade.php ENDPATH**/ ?>