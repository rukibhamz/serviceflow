<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = env('APP_INSTALLED') === 'true'
            || file_exists(storage_path('install.lock'));

        // Allow installer routes through regardless of install state
        if ($request->is('install') || $request->is('install/*')) {
            // If already installed, block access to installer
            if ($isInstalled) {
                return redirect('/');
            }
            return $next($request);
        }

        // Block all other routes until the app is installed
        if (! $isInstalled) {
            return redirect()->route('installer.index');
        }

        return $next($request);
    }
}
