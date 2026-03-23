<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/_internal_storage/framework/maintenance.php')) {
    require $maintenance;
}

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Illuminate\Http\Request::capture());
