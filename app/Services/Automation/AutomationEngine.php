<?php

namespace App\Services\Automation;

use App\Models\Automation;
use App\Models\AutomationLog;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

/**
 * Core automation engine.
 *
 * Called by event listeners when a trigger event fires.
 * Loads all active automations for the trigger, evaluates conditions,
 * executes matching actions, and logs results to automation_logs.
 */
class AutomationEngine
{
    public function __construct(
        private readonly ConditionEvaluator $evaluator,
        private readonly ActionExecutor $executor,
    ) {}

    /**
     * Process all active automations for the given trigger event and ticket.
     *
     * @param  string  $triggerEvent  e.g. 'ticket.created', 'ticket.updated'
     */
    public function process(string $triggerEvent, Ticket $ticket): void
    {
        $automations = Automation::where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->get();

        foreach ($automations as $automation) {
            $this->runAutomation($automation, $ticket);
        }
    }

    private function runAutomation(Automation $automation, Ticket $ticket): void
    {
        try {
            $conditions = $automation->conditions ?? [];
            $matched    = $this->evaluator->evaluate($conditions, $ticket);

            if (! $matched) {
                return;
            }

            $actions = $automation->actions ?? [];
            $this->executor->executeAll($actions, $ticket);

            // Update run stats
            $automation->increment('run_count');
            $automation->last_run_at = now();
            $automation->saveQuietly();

            AutomationLog::create([
                'automation_id'        => $automation->id,
                'ticket_id'            => $ticket->id,
                'conditions_snapshot'  => $conditions,
                'actions_executed'     => $actions,
                'result'               => 'success',
            ]);
        } catch (\Throwable $e) {
            Log::error('AutomationEngine: automation failed', [
                'automation_id' => $automation->id,
                'ticket_id'     => $ticket->id,
                'error'         => $e->getMessage(),
            ]);

            AutomationLog::create([
                'automation_id'        => $automation->id,
                'ticket_id'            => $ticket->id,
                'conditions_snapshot'  => $automation->conditions ?? [],
                'actions_executed'     => [],
                'result'               => 'error: ' . $e->getMessage(),
            ]);
        }
    }
}
