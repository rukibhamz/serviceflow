<?php

/**
 * Property 2: Status Transition Validity
 * Validates: Requirements 2.3
 *
 * Generate random sequences of status strings; assert only transitions in the
 * allowed graph are accepted and all others throw InvalidStatusTransitionException.
 */

use App\Exceptions\InvalidStatusTransitionException;
use App\Services\Tickets\TicketStatusMachine;

$machine = new TicketStatusMachine;

// The canonical allowed transition graph
$allowedGraph = [
    'open'        => ['in_progress'],
    'in_progress' => ['pending', 'resolved'],
    'pending'     => ['in_progress', 'resolved'],
    'resolved'    => ['closed', 'open'],
    'closed'      => ['open'],
];

$validStatuses = ['open', 'in_progress', 'pending', 'resolved', 'closed'];

// ── Unit tests ────────────────────────────────────────────────────────────────

test('canTransition returns true for every explicitly allowed edge', function () use ($machine, $allowedGraph) {
    foreach ($allowedGraph as $from => $targets) {
        foreach ($targets as $to) {
            expect($machine->canTransition($from, $to))->toBeTrue(
                "Expected {$from} → {$to} to be allowed"
            );
        }
    }
});

test('canTransition returns false for every disallowed pair of valid statuses', function () use ($machine, $allowedGraph, $validStatuses) {
    foreach ($validStatuses as $from) {
        foreach ($validStatuses as $to) {
            $allowed = in_array($to, $allowedGraph[$from] ?? [], true);
            expect($machine->canTransition($from, $to))->toBe($allowed,
                "canTransition({$from}, {$to}) should be " . ($allowed ? 'true' : 'false')
            );
        }
    }
});

test('canTransition returns false for unknown status strings', function () use ($machine) {
    expect($machine->canTransition('open', 'unknown'))->toBeFalse();
    expect($machine->canTransition('unknown', 'open'))->toBeFalse();
    expect($machine->canTransition('', ''))->toBeFalse();
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 2: Status Transition Validity
 *
 * For 100 random (from, to) pairs drawn from the full status vocabulary
 * (valid statuses + a handful of invalid strings), assert:
 *   - allowed pairs  → canTransition returns true
 *   - disallowed pairs → canTransition returns false AND transition() throws
 */
it('only accepts transitions present in the allowed graph across random pairs', function () use ($machine, $allowedGraph, $validStatuses) {
    // Extend the pool with some invalid status strings to exercise unknown inputs
    $invalidStatuses = ['', 'unknown', 'draft', 'archived', 'new', 'done'];
    $pool = array_merge($validStatuses, $invalidStatuses);

    $iterations = 100;
    $rand = new Random\Engine\Mt19937;

    for ($i = 0; $i < $iterations; $i++) {
        $from = $pool[random_int(0, count($pool) - 1)];
        $to   = $pool[random_int(0, count($pool) - 1)];

        $expectedAllowed = in_array($to, $allowedGraph[$from] ?? [], true);

        // canTransition must agree with the graph
        expect($machine->canTransition($from, $to))->toBe(
            $expectedAllowed,
            "Iteration {$i}: canTransition('{$from}', '{$to}') should be "
            . ($expectedAllowed ? 'true' : 'false')
        );

        // For disallowed transitions on valid statuses, transition() must throw
        if (! $expectedAllowed && in_array($from, $validStatuses, true)) {
            $ticket = new \App\Models\Ticket;
            $ticket->status = $from;

            expect(fn () => $machine->transition($ticket, $to))
                ->toThrow(InvalidStatusTransitionException::class);
        }
    }
})->repeat(100);
