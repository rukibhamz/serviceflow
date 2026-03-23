<?php

/**
 * Property 10: Automation Condition Fidelity
 * Validates: Requirements 8.2
 *
 * For random condition trees and matching/non-matching ticket contexts,
 * assert the ConditionEvaluator returns true iff the context satisfies
 * all conditions (AND) or at least one condition (OR).
 */

use App\Models\Ticket;
use App\Models\User;
use App\Services\Automation\ConditionEvaluator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => Event::fake());

// ── Unit tests ────────────────────────────────────────────────────────────────

test('evaluator returns true for empty condition tree', function () {
    $requester = User::factory()->create();
    $ticket    = makeTicket($requester, 'high', 'open');

    $evaluator = new ConditionEvaluator();

    expect($evaluator->evaluate([], $ticket))->toBeTrue()
        ->and($evaluator->evaluate(['operator' => 'AND', 'conditions' => []], $ticket))->toBeTrue();
});

test('evaluator matches equals condition correctly', function () {
    $requester = User::factory()->create();
    $ticket    = makeTicket($requester, 'high', 'open');

    $evaluator = new ConditionEvaluator();

    $tree = ['operator' => 'AND', 'conditions' => [
        ['field' => 'priority', 'op' => 'equals', 'value' => 'high'],
    ]];

    expect($evaluator->evaluate($tree, $ticket))->toBeTrue();

    $tree['conditions'][0]['value'] = 'low';
    expect($evaluator->evaluate($tree, $ticket))->toBeFalse();
});

test('evaluator AND requires all conditions to match', function () {
    $requester = User::factory()->create();
    $ticket    = makeTicket($requester, 'high', 'open');

    $evaluator = new ConditionEvaluator();

    $tree = ['operator' => 'AND', 'conditions' => [
        ['field' => 'priority', 'op' => 'equals', 'value' => 'high'],
        ['field' => 'status',   'op' => 'equals', 'value' => 'closed'], // won't match
    ]];

    expect($evaluator->evaluate($tree, $ticket))->toBeFalse();
});

test('evaluator OR requires at least one condition to match', function () {
    $requester = User::factory()->create();
    $ticket    = makeTicket($requester, 'high', 'open');

    $evaluator = new ConditionEvaluator();

    $tree = ['operator' => 'OR', 'conditions' => [
        ['field' => 'priority', 'op' => 'equals', 'value' => 'low'],   // no match
        ['field' => 'status',   'op' => 'equals', 'value' => 'open'],  // match
    ]];

    expect($evaluator->evaluate($tree, $ticket))->toBeTrue();
});

test('evaluator supports contains operator', function () {
    $requester = User::factory()->create();
    $ticket    = makeTicket($requester, 'medium', 'open', 'Server is down urgently');

    $evaluator = new ConditionEvaluator();

    $tree = ['operator' => 'AND', 'conditions' => [
        ['field' => 'subject', 'op' => 'contains', 'value' => 'urgent'],
    ]];

    expect($evaluator->evaluate($tree, $ticket))->toBeTrue();

    $tree['conditions'][0]['value'] = 'billing';
    expect($evaluator->evaluate($tree, $ticket))->toBeFalse();
});

test('evaluator supports is_null and is_not_null operators', function () {
    $requester = User::factory()->create();
    $ticket    = makeTicket($requester, 'low', 'open');

    $evaluator = new ConditionEvaluator();

    $nullTree = ['operator' => 'AND', 'conditions' => [
        ['field' => 'assignee_id', 'op' => 'is_null'],
    ]];

    expect($evaluator->evaluate($nullTree, $ticket))->toBeTrue();

    $notNullTree = ['operator' => 'AND', 'conditions' => [
        ['field' => 'assignee_id', 'op' => 'is_not_null'],
    ]];

    expect($evaluator->evaluate($notNullTree, $ticket))->toBeFalse();
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 10: Automation Condition Fidelity
 *
 * For 100 random scenarios:
 *   - Pick a random field (priority, status, type) and a random value
 *   - Build an AND tree with one condition that MATCHES the ticket
 *   - Assert evaluator returns true
 *   - Build an AND tree with one condition that does NOT match
 *   - Assert evaluator returns false
 */
it('correctly evaluates matching and non-matching single-condition AND trees', function () {
    $priorities = ['low', 'medium', 'high', 'critical'];
    $statuses   = ['open', 'in_progress', 'pending', 'resolved', 'closed'];
    $types      = ['incident', 'service_request', 'problem', 'change'];

    $requester = User::factory()->create();
    $evaluator = new ConditionEvaluator();

    // Pick random values for this iteration
    $priority = $priorities[array_rand($priorities)];
    $status   = $statuses[array_rand($statuses)];
    $type     = $types[array_rand($types)];

    $ticket = makeTicket($requester, $priority, $status, 'Property test ' . bin2hex(random_bytes(4)), $type);

    // Matching tree
    $field = collect(['priority', 'status', 'type'])->random();
    $value = $ticket->{$field};

    $matchTree = ['operator' => 'AND', 'conditions' => [
        ['field' => $field, 'op' => 'equals', 'value' => $value],
    ]];

    expect($evaluator->evaluate($matchTree, $ticket))->toBeTrue(
        "Expected true for {$field}='{$value}' on ticket with {$field}='{$ticket->{$field}}'"
    );

    // Non-matching tree — use a value that differs from the ticket's actual value
    $otherValues = match ($field) {
        'priority' => array_diff($priorities, [$value]),
        'status'   => array_diff($statuses, [$value]),
        'type'     => array_diff($types, [$value]),
        default    => ['__no_match__'],
    };

    $nonMatchValue = array_values($otherValues)[0];

    $noMatchTree = ['operator' => 'AND', 'conditions' => [
        ['field' => $field, 'op' => 'equals', 'value' => $nonMatchValue],
    ]];

    expect($evaluator->evaluate($noMatchTree, $ticket))->toBeFalse(
        "Expected false for {$field}='{$nonMatchValue}' on ticket with {$field}='{$ticket->{$field}}'"
    );
})->repeat(100);

// ── Helper ────────────────────────────────────────────────────────────────────

function makeTicket(User $requester, string $priority, string $status, string $subject = 'Test ticket', string $type = 'incident'): Ticket
{
    return Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => $subject,
        'priority'     => $priority,
        'type'         => $type,
        'status'       => $status,
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);
}
