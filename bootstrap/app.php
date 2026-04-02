<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withEvents(discover: [__DIR__.'/../app/Listeners'])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'install/*',
            'logout',
            'login',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $e) {
            file_put_contents(base_path('_internal_storage/logs/405_debug.log'), '[' . date('Y-m-d H:i:s') . '] Exception: ' . get_class($e) . ' - ' . $e->getMessage() . ' | ' . request()->method() . ' ' . request()->fullUrl() . PHP_EOL, FILE_APPEND);
        });
    })
    ->create()
    ->usePublicPath(base_path())
    ->useStoragePath(base_path('_internal_storage'));

