<?php $__env->startSection('page-header'); ?>
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">IT Asset Management</div>
            <div class="page-sub">Track hardware, software, and infrastructure assets</div>
        </div>
        <button class="btn-ds primary">+ Register Asset</button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $assetStats = [
        'total'    => \App\Models\Asset::count(),
        'active'   => \App\Models\Asset::where('status', 'active')->count(),
        'inactive' => \App\Models\Asset::where('status', 'inactive')->count(),
        'assigned' => \App\Models\Asset::whereNotNull('assigned_to')->count(),
    ];
?>

<div class="stats-ds mb-4">
    <div class="stat-card">
        <div class="stat-label">Total Assets</div>
        <div class="stat-val text-brand"><?php echo e($assetStats['total']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active</div>
        <div class="stat-val text-success"><?php echo e($assetStats['active']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Assigned</div>
        <div class="stat-val"><?php echo e($assetStats['assigned']); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Inactive</div>
        <div class="stat-val text-gray-400"><?php echo e($assetStats['inactive']); ?></div>
    </div>
</div>

<div class="card-ds">
    <div class="card-hdr">
        <div class="card-title">Asset Register</div>
    </div>
    <div class="card-body py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
        </svg>
        <div class="text-gray-400 font-medium mb-1">Asset UI in progress</div>
        <div class="text-xs text-gray-300">The full asset table, tagging, and association with tickets will be available shortly.</div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/agent/itsm/assets.blade.php ENDPATH**/ ?>