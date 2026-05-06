<?php $__env->startSection('page-header'); ?>
    <div class="page-title">Automation Rules</div>
    <div class="page-sub">Define triggers and actions to automate your workflow</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card-ds">
    <div class="card-hdr">
        <div class="card-title">Automation Rules</div>
        <button class="btn-ds primary">+ New Rule</button>
    </div>
    <div class="card-body py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <div class="text-gray-400 font-medium mb-1">Automation Builder coming in Phase 2</div>
        <div class="text-xs text-gray-300">No-code rule editor with triggers (ticket created, SLA breached) and actions (assign, notify, tag) coming soon.</div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/agent/automation/index.blade.php ENDPATH**/ ?>