<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e(config('app.name', 'ServiceFlow')); ?></title>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="antialiased bg-gray-50">
        <div class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-brand-700 mb-4">ServiceFlow</h1>
                <p class="text-gray-600">Enterprise Service Desk</p>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\welcome.blade.php ENDPATH**/ ?>