
<?php $currentStep = 2; ?>

<?php $__env->startSection('content'); ?>
<h2 class="text-xl font-semibold text-gray-800 mb-4">Database Configuration</h2>
<p class="text-gray-500 mb-6 text-sm">Enter your database credentials. ServiceFlow will run migrations and seed initial data.</p>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <p><?php echo e($error); ?></p>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<form method="POST" action="<?php echo e(route('installer.database.store')); ?>" class="space-y-4">
    <?php echo csrf_field(); ?>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Connection</label>
        <select name="connection" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="mysql" <?php echo e(old('connection', 'mysql') === 'mysql' ? 'selected' : ''); ?>>MySQL</option>
            <option value="pgsql" <?php echo e(old('connection') === 'pgsql' ? 'selected' : ''); ?>>PostgreSQL</option>
        </select>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
            <input type="text" name="host" value="<?php echo e(old('host', '127.0.0.1')); ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
            <input type="text" name="port" value="<?php echo e(old('port', '3306')); ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
        <input type="text" name="database" value="<?php echo e(old('database')); ?>"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="username" value="<?php echo e(old('username')); ?>"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="flex justify-between pt-2">
        <a href="<?php echo e(route('installer.index')); ?>" class="text-sm text-gray-500 hover:underline self-center">← Back</a>
        <button type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
            Test &amp; Install →
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('installer.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\installer\database.blade.php ENDPATH**/ ?>