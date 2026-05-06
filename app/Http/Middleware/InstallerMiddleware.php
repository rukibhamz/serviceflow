<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // If already installed, block access to the installer and redirect home
        if (env('APP_INSTALLED') === 'true' || file_exists(storage_path('install.lock'))) {
            return redirect('/');
        }

        return $next($request);
    }
}
