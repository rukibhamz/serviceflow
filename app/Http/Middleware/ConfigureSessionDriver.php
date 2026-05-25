<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps installer requests on file sessions and avoids database sessions
 * when the sessions table has not been created yet.
 */
class ConfigureSessionDriver
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install') || $request->is('install/*')) {
            config(['session.driver' => 'file']);

            return $next($request);
        }

        if (config('session.driver') !== 'database') {
            return $next($request);
        }

        try {
            if (! Schema::hasTable(config('session.table', 'sessions'))) {
                config(['session.driver' => 'file']);
            }
        } catch (\Throwable) {
            config(['session.driver' => 'file']);
        }

        return $next($request);
    }
}
