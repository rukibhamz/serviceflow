<?php

/**
 * ServiceFlow cPanel Entry Point
 * 
 * This file allows the application to be hosted in a standard cPanel environment
 * where the document root cannot be changed to the public/ directory.
 * All requests hitting the root directory are forwarded to Laravel's public entry point.
 */

require_once __DIR__.'/public/index.php';
