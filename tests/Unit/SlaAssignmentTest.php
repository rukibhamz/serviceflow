<?php

/**
 * Property 3: SLA Assignment on Ticket Creation
 * Validates: Requirements 3.1
 *
 * For any valid ticket creation, assert exactly one SlaPolicy is assigned and
 * all SlaTimer records created are linked to the correct policy matching the
 * ticket's priority.
 */

use App\Models\SlaPolicy;
use App\Models\SlaTimer;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Sla\SlaService;
use App\Services\Sla\BusinessHoursCalculator;
use Illuminate\Support\Facades\Event;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

// Fake all events to prevent activity_log writes (LogsActivity uses events internally)
beforeEach(fn () => Event::fake());

$priorities = ['low', 'medium', 'high', 'critical'];

// ── Unit tests ────────────────────────────────────────────────────────────────

test('assignPolicy creates SlaTimers linked to the matching priority policy', function () {
    $policy = SlaPolicy::create([
        'name'               => 'High SLA',
        'priority'           => 'high',
        'response_minutes'   => 60,
        'resolution_minutes' => 480,
        'is_active'          => true,
        'is_default'         => false,
    ]);

    $requester = User::factory()->create();
    $ticket = Ticket::create([
        'ulid'         => \Illuminate\Support\Str::ulid(),
        'subject'      => 'Test ticket',
        'priority'     => 'high',
        'type'         => 'incident',
        'status'       => 'open',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $service = new SlaService(new BusinessHoursCalculator);
    $service->assignPolicy($ticket);

    $timers = SlaTimer::where('ticket_id', $ticket->id)->get();

    expect($timers)->not->toBeEmpty();
    foreach ($timers as $timer) {
        expect($timer->sla_policy_id)->toBe($policy->id);
    }
});

test('assignPolicy creates no SlaTimers when no matching policy exists', function () {
    $requester = User::factory()->create();
    $ticket = Ticket::create([
        'ulid'         => \Illuminate\Support\Str::ulid(),
        'subject'      => 'No policy ticket',
        'priority'     => 'critical',
        'type'         => 'incident',
        'status'       => 'open',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $service = new SlaService(new BusinessHoursCalculator);
    $service->assignPolicy($ticket);

    expect(SlaTimer::where('ticket_id', $ticket->id)->count())->toBe(0);
});

test('assignPolicy uses default policy when no priority match exists', function () {
    $defaultPolicy = SlaPolicy::create([
        'name'               => 'Default SLA',
        'priority'           => 'low',
        'response_minutes'   => 240,
        'resolution_minutes' => 1440,
        'is_active'          => true,
        'is_default'         => true,
    ]);

    $requester = User::factory()->create();
    $ticket = Ticket::create([
        'ulid'         => \Illuminate\Support\Str::ulid(),
        'subject'      => 'Fallback ticket',
        'priority'     => 'critical', // no active policy for critical
        'type'         => 'incident',
        'status'       => 'open',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $service = new SlaService(new BusinessHoursCalculator);
    $service->assignPolicy($ticket);

    $timers = SlaTimer::where('ticket_id', $ticket->id)->get();

    expect($timers)->not->toBeEmpty();
    foreach ($timers as $timer) {
        expect($timer->sla_policy_id)->toBe($defaultPolicy->id);
    }
});

test('assignPolicy prefers specific priority match over default policy', function () {
    SlaPolicy::create([
        'name'               => 'Default SLA',
        'priority'           => 'low',
        'response_minutes'   => 240,
        'resolution_minutes' => 1440,
        'is_active'          => true,
        'is_default'         => true,
    ]);

    $specificPolicy = SlaPolicy::create([
        'name'               => 'Medium SLA',
        'priority'           => 'medium',
        'response_minutes'   => 120,
        'resolution_minutes' => 720,
        'is_active'          => true,
        'is_default'         => false,
    ]);

    $requester = User::factory()->create();
    $ticket = Ticket::create([
        'ulid'         => \Illuminate\Support\Str::ulid(),
        'subject'      => 'Priority match ticket',
        'priority'     => 'medium',
        'type'         => 'incident',
        'status'       => 'open',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $service = new SlaService(new BusinessHoursCalculator);
    $service->assignPolicy($ticket);

    $timers = SlaTimer::where('ticket_id', $ticket->id)->get();

    expect($timers)->not->toBeEmpty();
    foreach ($timers as $timer) {
        expect($timer->sla_policy_id)->toBe($specificPolicy->id);
    }
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 3: SLA Assignment on Ticket Creation
 *
 * For 100 random valid ticket creations (varying priorities):
 *   - Create one SlaPolicy per priority (low, medium, high, critical)
 *   - Generate a ticket with a random priority
 *   - Call SlaService::assignPolicy on the ticket
 *   - Assert at least one SlaTimer is created for the ticket
 *   - Assert every SlaTimer is linked to the SlaPolicy matching the ticket's priority
 */
it('assigns SlaTimers linked to the correct policy for any valid ticket priority', function () use ($priorities) {
    // Create one active SLA policy per priority
    $policies = [];
    foreach ($priorities as $priority) {
        $policies[$priority] = SlaPolicy::create([
            'name'               => ucfirst($priority) . ' SLA',
            'priority'           => $priority,
            'response_minutes'   => match ($priority) {
                'critical' => 15,
                'high'     => 60,
                'medium'   => 120,
                'low'      => 240,
            },
            'resolution_minutes' => match ($priority) {
                'critical' => 240,
                'high'     => 480,
                'medium'   => 720,
                'low'      => 1440,
            },
            'is_active'  => true,
            'is_default' => false,
        ]);
    }

    $requester = User::factory()->create();
    $service   = new SlaService(new BusinessHoursCalculator);

    // Pick a random priority for this iteration
    $priority = $priorities[array_rand($priorities)];

    $ticket = Ticket::create([
        'ulid'         => \Illuminate\Support\Str::ulid(),
        'subject'      => 'Property test ticket ' . bin2hex(random_bytes(4)),
        'priority'     => $priority,
        'type'         => 'incident',
        'status'       => 'open',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $service->assignPolicy($ticket);

    $timers = SlaTimer::where('ticket_id', $ticket->id)->get();

    // At least one timer must be created
    expect($timers->count())->toBeGreaterThan(0,
        "Expected at least one SlaTimer for ticket with priority '{$priority}', got 0"
    );

    $expectedPolicyId = $policies[$priority]->id;

    // Every timer must reference the policy matching the ticket's priority
    foreach ($timers as $timer) {
        expect($timer->sla_policy_id)->toBe($expectedPolicyId,
            "SlaTimer type '{$timer->type}' linked to policy {$timer->sla_policy_id}, expected policy {$expectedPolicyId} for priority '{$priority}'"
        );
    }
})->repeat(100);
