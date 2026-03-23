<?php

namespace App\Services\Automation;

/**
 * Registry of supported automation trigger event names.
 *
 * Each trigger maps to the Laravel event class that fires it.
 * The AutomationEngine listens for these events and evaluates
 * active automations whose trigger matches.
 */
class TriggerRegistry
{
    /** Map of trigger name → event class */
    private const TRIGGERS = [
        'ticket.created' => \App\Events\TicketCreated::class,
        'ticket.updated' => \App\Events\TicketUpdated::class,
        'comment.added'  => \App\Events\CommentAdded::class,
        'sla.breached'   => \App\Events\SlaBreached::class,
    ];

    /**
     * Returns all registered trigger names.
     *
     * @return string[]
     */
    public function all(): array
    {
        return array_keys(self::TRIGGERS);
    }

    /**
     * Returns the event class for a given trigger name, or null if unknown.
     */
    public function eventClass(string $trigger): ?string
    {
        return self::TRIGGERS[$trigger] ?? null;
    }

    /**
     * Returns true if the trigger name is registered.
     */
    public function has(string $trigger): bool
    {
        return isset(self::TRIGGERS[$trigger]);
    }

    /**
     * Returns the trigger name for a given event class, or null if not mapped.
     */
    public function triggerForEvent(string $eventClass): ?string
    {
        $flipped = array_flip(self::TRIGGERS);

        return $flipped[$eventClass] ?? null;
    }
}
