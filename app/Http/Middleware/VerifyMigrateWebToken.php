<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMigrateWebToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('serviceflow.migrate_web_token');

        if (! is_string($token) || $token === '') {
            abort(404);
        }

        $provided = $request->header('X-Migrate-Token');
        if (! is_string($provided) || ! hash_equals($token, $provided)) {
            abort(403);
        }

        return $next($request);
    }
}
