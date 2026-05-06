<?php $__env->startSection('page-header'); ?>
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">Change Management</div>
            <div class="page-sub">Track and approve infrastructure and software changes</div>
        </div>
        <button class="btn-ds primary">+ New Change Request</button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card-ds">
    <div class="card-hdr">
        <div class="card-title">Change Requests</div>
        <span class="text-xs text-gray-400">Coming in Phase 2</span>
    </div>
    <div class="card-body py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <div class="text-gray-400 font-medium mb-1">Change Management is coming soon</div>
        <div class="text-xs text-gray-300">JSON-based approval workflows, CAB calendar, and risk scoring will be available in Phase 2.</div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/agent/itsm/changes.blade.php ENDPATH**/ ?>