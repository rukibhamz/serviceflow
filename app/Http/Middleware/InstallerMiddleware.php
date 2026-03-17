<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (env('APP_INSTALLED') === 'true') {
            return redirect('/');
        }

        return $next($request);
    }
}
