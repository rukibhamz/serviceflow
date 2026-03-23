<?php

namespace App\Services\Automation;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\Tickets\TicketStatusMachine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Executes automation actions against a ticket.
 *
 * Supported action types (set in automation.actions JSON array):
 *
 *   { "type": "assign_ticket",    "assignee_id": 5 }
 *   { "type": "change_status",    "status": "in_progress" }
 *   { "type": "add_comment",      "body": "Auto-reply: ...", "internal": true }
 *   { "type": "send_notification","user_id": 5, "message": "..." }
 *   { "type": "trigger_webhook",  "url": "https://...", "method": "POST" }
 */
class ActionExecutor
{
    public function __construct(
        private readonly TicketStatusMachine $statusMachine,
    ) {}

    /**
     * Execute a list of action definitions against the given ticket.
     *
     * @param  array<int, array<string, mixed>>  $actions
     */
    public function executeAll(array $actions, Ticket $ticket): void
    {
        foreach ($actions as $action) {
            try {
                $this->execute($action, $ticket);
            } catch (\Throwable $e) {
                Log::warning('AutomationEngine: action failed', [
                    'action'    => $action,
                    'ticket_id' => $ticket->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Execute a single action definition.
     *
     * @param  array<string, mixed>  $action
     */
    public function execute(array $action, Ticket $ticket): void
    {
        match ($action['type'] ?? '') {
            'assign_ticket'     => $this->assignTicket($ticket, (int) $action['assignee_id']),
            'change_status'     => $this->changeStatus($ticket, (string) $action['status']),
            'add_comment'       => $this->addComment($ticket, (string) $action['body'], (bool) ($action['internal'] ?? true)),
            'send_notification' => $this->sendNotification($ticket, (int) $action['user_id'], (string) $action['message']),
            'trigger_webhook'   => $this->triggerWebhook($ticket, (string) $action['url'], (string) ($action['method'] ?? 'POST')),
            default             => Log::warning('AutomationEngine: unknown action type', ['action' => $action]),
        };
    }

    // ── Action implementations ────────────────────────────────────────────────

    private function assignTicket(Ticket $ticket, int $assigneeId): void
    {
        $user = User::find($assigneeId);

        if ($user === null) {
            Log::warning('AutomationEngine: assignee not found', ['assignee_id' => $assigneeId]);
            return;
        }

        $ticket->assignee_id = $assigneeId;
        $ticket->save();
    }

    private function changeStatus(Ticket $ticket, string $newStatus): void
    {
        $this->statusMachine->transition($ticket, $newStatus);
    }

    private function addComment(Ticket $ticket, string $body, bool $internal = true): void
    {
        TicketComment::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => null, // system comment
            'body'        => $body,
            'is_internal' => $internal,
        ]);
    }

    private function sendNotification(Ticket $ticket, int $userId, string $message): void
    {
        $user = User::find($userId);

        if ($user === null) {
            return;
        }

        // Use Laravel's built-in database notification channel
        $user->notify(new \App\Notifications\AutomationNotification($ticket, $message));
    }

    private function triggerWebhook(Ticket $ticket, string $url, string $method = 'POST'): void
    {
        Http::timeout(10)->{strtolower($method)}($url, [
            'ticket_id' => $ticket->id,
            'ulid'      => $ticket->ulid,
            'status'    => $ticket->status,
            'priority'  => $ticket->priority,
        ]);
    }
}
