<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MigrateWebController extends Controller
{
    /**
     * Run pending migrations (invoked only when MIGRATE_WEB_TOKEN is set and verified).
     */
    public function __invoke(): JsonResponse
    {
        Log::warning('serviceflow web migrate invoked', ['ip' => request()->ip()]);

        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Migration failed.',
            ], 500);
        }

        return response()->json([
            'ok' => $exitCode === 0,
            'exit_code' => $exitCode,
            'output' => $output,
        ], $exitCode === 0 ? 200 : 500);
    }
}
