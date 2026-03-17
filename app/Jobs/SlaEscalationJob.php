<?php

namespace App\Jobs;

use App\Models\SlaTimer;
use App\Services\Sla\SlaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SlaEscalationJob implements ShouldQueue
{
    use Queueable;

    public function handle(SlaService $slaService): void
    {
        SlaTimer::whereNull('stopped_at')
            ->where('breached', false)
            ->each(function (SlaTimer $timer) use ($slaService) {
                $slaService->checkBreach($timer);
            });
    }
}
