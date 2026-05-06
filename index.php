<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

// Auto-bootstrap .env and APP_KEY for fresh installs before Laravel boots
(function () {
    $envPath     = __DIR__ . '/.env';
    $examplePath = __DIR__ . '/.env.example';

    // Copy .env.example to .env if missing
    if (! file_exists($envPath) && file_exists($examplePath)) {
        copy($examplePath, $envPath);
    }

    // Generate APP_KEY if missing or empty
    if (file_exists($envPath)) {
        $contents = file_get_contents($envPath);
        if (preg_match('/^APP_KEY=\s*$/m', $contents)) {
            $key      = 'base64:' . base64_encode(random_bytes(32));
            $contents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $contents);
            file_put_contents($envPath, $contents);
        }
    }

    // Ensure required _internal_storage directories exist
    $dirs = [
        __DIR__ . '/_internal_storage/framework/sessions',
        __DIR__ . '/_internal_storage/framework/views',
        __DIR__ . '/_internal_storage/framework/cache/data',
        __DIR__ . '/_internal_storage/app/public',
        __DIR__ . '/_internal_storage/logs',
    ];
    foreach ($dirs as $dir) {
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
})();

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/_internal_storage/framework/maintenance.php')) {
    require $maintenance;
}

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Illuminate\Http\Request::capture());
