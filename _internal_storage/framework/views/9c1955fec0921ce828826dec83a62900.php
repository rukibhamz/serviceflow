<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $__env->yieldContent('title', 'Support Portal'); ?> — ServiceFlow</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <nav class="border-b bg-white px-6 py-3 flex items-center justify-between">
        <a href="<?php echo e(route('portal.index')); ?>" class="font-semibold text-blue-600">Support Portal</a>
        <div class="flex items-center gap-4 text-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('portal.tickets.index')); ?>" class="text-gray-600 hover:text-gray-900">My Tickets</a>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="text-gray-500 hover:text-gray-900">Sign out</button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="text-blue-600 hover:underline">Sign in</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </nav>

    <main class="mx-auto max-w-4xl px-4 py-8">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\portal\layout.blade.php ENDPATH**/ ?>