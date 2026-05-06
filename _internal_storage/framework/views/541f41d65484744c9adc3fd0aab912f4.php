<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $__env->yieldContent('title', 'Support Portal'); ?> — ServiceFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'sans-serif'] } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="<?php echo e(asset('css/design-system.css')); ?>">
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <nav class="border-b bg-white px-6 py-3 flex items-center justify-between">
        <a href="<?php echo e(route('portal.index')); ?>" class="font-semibold text-blue-600">Support Portal</a>
        <div class="flex items-center gap-4 text-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('portal.index')); ?>" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                <a href="<?php echo e(route('portal.tickets.index')); ?>" class="text-gray-600 hover:text-gray-900">My Tickets</a>
                <a href="<?php echo e(route('logout')); ?>" class="text-gray-500 hover:text-gray-900">Sign out</a>
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
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/portal/layout.blade.php ENDPATH**/ ?>