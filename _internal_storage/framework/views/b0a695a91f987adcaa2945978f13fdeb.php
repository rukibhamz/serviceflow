<?php $__env->startSection('page-header'); ?>
    <div class="page-title">Reports</div>
    <div class="page-sub">Analyse team performance, SLA compliance, and ticket trends</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card-ds">
    <div class="card-hdr">
        <div class="card-title">Report Builder</div>
        <span class="text-xs text-gray-400">Coming in Phase 3</span>
    </div>
    <div class="card-body py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <div class="text-gray-400 font-medium mb-1">Custom reports coming soon</div>
        <div class="text-xs text-gray-300">Report builder with Excel/PDF exports and scheduled delivery will be available in Phase 3.</div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/agent/reports/index.blade.php ENDPATH**/ ?>