<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceFlow Installer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'sans-serif'] } } } }</script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="w-full max-w-2xl mx-auto py-10 px-4">

    
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">ServiceFlow</h1>
        <p class="text-gray-500 mt-1">Installation Wizard</p>
    </div>

    
    <div class="flex items-center justify-center mb-8 space-x-2 text-sm">
        <?php
            $steps = [
                1 => ['label' => 'Environment', 'route' => 'installer.index'],
                2 => ['label' => 'Database',    'route' => 'installer.database'],
                3 => ['label' => 'Admin Account','route' => 'installer.account'],
                4 => ['label' => 'Finish',       'route' => 'installer.finish'],
            ];
            $currentStep = $currentStep ?? 1;
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-8 h-8 rounded-full font-semibold
                    <?php echo e($num < $currentStep ? 'bg-green-500 text-white' : ($num === $currentStep ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600')); ?>">
                    <?php echo e($num < $currentStep ? '✓' : $num); ?>

                </div>
                <span class="ml-1 <?php echo e($num === $currentStep ? 'text-blue-600 font-semibold' : 'text-gray-500'); ?>">
                    <?php echo e($step['label']); ?>

                </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                    <span class="mx-2 text-gray-300">→</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="bg-white rounded-xl shadow-md p-8">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/installer/layout.blade.php ENDPATH**/ ?>