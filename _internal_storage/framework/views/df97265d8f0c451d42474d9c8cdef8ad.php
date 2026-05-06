<?php $__env->startSection('page-header'); ?>
    <div class="page-title">My Profile</div>
    <div class="page-sub">Manage your personal information and preferences</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php $user = auth()->user(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    
    <div class="card-ds text-center p-6">
        <div class="w-20 h-20 rounded-full bg-accent flex items-center justify-center mx-auto mb-4 text-white text-3xl font-semibold">
            <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

        </div>
        <div class="font-semibold text-gray-900 text-lg"><?php echo e($user->name); ?></div>
        <div class="text-sm text-gray-400 mt-1"><?php echo e($user->email); ?></div>
        <div class="mt-2">
            <span class="badge-ds <?php echo e($user->role === 'admin' ? 'open' : ($user->role === 'agent' ? 'inprog' : 'low')); ?>">
                <?php echo e(ucfirst($user->role)); ?>

            </span>
        </div>
        <div class="mt-4 text-xs text-gray-400">Member since <?php echo e($user->created_at->format('M Y')); ?></div>
    </div>

    
    <div class="card-ds lg:col-span-2">
        <div class="card-hdr">
            <div class="card-title">Personal Information</div>
        </div>
        <div class="card-body space-y-4">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-input-ds" value="<?php echo e($user->name); ?>" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-input-ds" value="<?php echo e($user->email); ?>" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <input type="text" class="form-input-ds" value="<?php echo e(ucfirst($user->role)); ?>" readonly>
            </div>
            <p class="text-xs text-gray-400">Profile editing, avatar upload, and password change coming in Phase 2.</p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/agent/settings/profile.blade.php ENDPATH**/ ?>