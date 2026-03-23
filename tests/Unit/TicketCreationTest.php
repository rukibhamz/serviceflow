<?php

/**
 * Property 1: Ticket Creation Invariants
 * Validates: Requirements 2.1
 *
 * Generate arbitrary valid ticket payloads; assert ticket persists with correct
 * fields and `TicketCreated` event is dispatched.
 */

use App\Actions\Tickets\CreateTicketAction;
use App\Events\TicketCreated;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Event;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

$priorities = ['low', 'medium', 'high', 'critical'];
$types      = ['incident', 'service_request', 'change', 'problem'];
$sources    = ['web', 'email', 'api'];

// ── Unit tests ────────────────────────────────────────────────────────────────

test('creates a ticket with the correct fields', function () {
    Event::fake();

    $requester = User::factory()->create();
    $action    = new CreateTicketAction;

    $ticket = $action->execute([
        'subject'  => 'Test subject',
        'priority' => 'high',
        'type'     => 'incident',
        'source'   => 'web',
    ], $requester);

    expect($ticket)->toBeInstanceOf(Ticket::class)
        ->and($ticket->subject)->toBe('Test subject')
        ->and($ticket->priority)->toBe('high')
        ->and($ticket->type)->toBe('incident')
        ->and($ticket->status)->toBe('open')
        ->and($ticket->requester_id)->toBe($requester->id)
        ->and($ticket->ulid)->not->toBeEmpty();

    $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
});

test('dispatches TicketCreated event on successful creation', function () {
    Event::fake();

    $requester = User::factory()->create();
    $action    = new CreateTicketAction;

    $ticket = $action->execute([
        'subject'  => 'Event test',
        'priority' => 'low',
        'type'     => 'problem',
    ], $requester);

    Event::assertDispatched(TicketCreated::class, function ($event) use ($ticket) {
        return $event->ticket->id === $ticket->id;
    });
});

test('throws ValidationException for empty subject', function () {
    $requester = User::factory()->make(['id' => 1]);
    $action    = new CreateTicketAction;

    expect(fn () => $action->execute([
        'subject'  => '',
        'priority' => 'low',
        'type'     => 'incident',
    ], $requester))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('throws ValidationException for invalid priority', function () {
    $requester = User::factory()->make(['id' => 1]);
    $action    = new CreateTicketAction;

    expect(fn () => $action->execute([
        'subject'  => 'Valid subject',
        'priority' => 'urgent',
        'type'     => 'incident',
    ], $requester))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('throws ValidationException for invalid type', function () {
    $requester = User::factory()->make(['id' => 1]);
    $action    = new CreateTicketAction;

    expect(fn () => $action->execute([
        'subject'  => 'Valid subject',
        'priority' => 'low',
        'type'     => 'unknown_type',
    ], $requester))->toThrow(\Illuminate\Validation\ValidationException::class);
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 1: Ticket Creation Invariants
 *
 * For 100 random valid payloads (varying subjects, priorities, types, sources),
 * assert:
 *   - The ticket is persisted in the database with the exact fields supplied
 *   - The ticket status is always 'open'
 *   - The ticket's requester_id matches the provided requester
 *   - A TicketCreated event is dispatched carrying the created ticket
 */
it('persists ticket with correct fields and dispatches TicketCreated for arbitrary valid payloads', function () use ($priorities, $types, $sources) {
    Event::fake();

    $requester = User::factory()->create();
    $action    = new CreateTicketAction;

    $priority = $priorities[array_rand($priorities)];
    $type     = $types[array_rand($types)];
    $source   = $sources[array_rand($sources)];
    $subject  = 'Subject ' . bin2hex(random_bytes(8));

    $ticket = $action->execute([
        'subject'     => $subject,
        'description' => 'Description ' . bin2hex(random_bytes(4)),
        'priority'    => $priority,
        'type'        => $type,
        'source'      => $source,
    ], $requester);

    // Field correctness
    expect($ticket->subject)->toBe($subject)
        ->and($ticket->priority)->toBe($priority)
        ->and($ticket->type)->toBe($type)
        ->and($ticket->source)->toBe($source)
        ->and($ticket->status)->toBe('open')
        ->and($ticket->requester_id)->toBe($requester->id)
        ->and($ticket->ulid)->not->toBeEmpty();

    // Persisted in DB
    $this->assertDatabaseHas('tickets', [
        'id'           => $ticket->id,
        'subject'      => $subject,
        'priority'     => $priority,
        'type'         => $type,
        'source'       => $source,
        'status'       => 'open',
        'requester_id' => $requester->id,
    ]);

    // TicketCreated event dispatched with the correct ticket
    Event::assertDispatched(TicketCreated::class, function ($event) use ($ticket) {
        return $event->ticket->id === $ticket->id;
    });
})->repeat(100);
