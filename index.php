<?php

/**
 * XAMPP subdirectory shim.
 * Forwards all requests to public/index.php while keeping paths correct.
 */

// Point Laravel's path resolution to the public directory
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['SCRIPT_NAME']     = str_replace('/index.php', '/public/index.php', $_SERVER['SCRIPT_NAME'] ?? '/index.php');

require __DIR__ . '/public/index.php';
