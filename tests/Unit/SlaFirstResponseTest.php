<?php

/**
 * Property 4: First Response Stops SLA Timer
 * Validates: Requirements 3.2
 *
 * Post first agent comment on any open ticket; assert `stopped_at` (first_response_at)
 * is set on the response SlaTimer and subsequent comments do not overwrite it.
 */

use App\Models\SlaPolicy;
use App\Models\SlaTimer;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\Sla\BusinessHoursCalculator;
use App\Services\Sla\SlaService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

// Fake events to prevent side-effects (activity log, mail, etc.)
beforeEach(fn () => Event::fake());

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeTicketWithResponseTimer(): array
{
    $policy = SlaPolicy::create([
        'name'               => 'Test SLA',
        'priority'           => 'medium',
        'response_minutes'   => 120,
        'resolution_minutes' => 480,
        'is_active'          => true,
        'is_default'         => false,
    ]);

    $requester = User::factory()->create();
    $agent     = User::factory()->create();

    $ticket = Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Test ticket ' . bin2hex(random_bytes(4)),
        'priority'     => 'medium',
        'type'         => 'incident',
        'status'       => 'open',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $timer = SlaTimer::create([
        'ticket_id'     => $ticket->id,
        'sla_policy_id' => $policy->id,
        'type'          => 'response',
        'due_at'        => now()->addHours(2),
        'breached'      => false,
    ]);

    return [$ticket, $timer, $agent];
}

// ── Unit tests ────────────────────────────────────────────────────────────────

test('recordFirstResponse sets stopped_at on the response timer', function () {
    [$ticket, $timer, $agent] = makeTicketWithResponseTimer();

    expect($timer->stopped_at)->toBeNull();

    $comment = TicketComment::create([
        'ticket_id'   => $ticket->id,
        'user_id'     => $agent->id,
        'body'        => 'First agent response',
        'is_internal' => false,
        'is_system'   => false,
    ]);

    $service = new SlaService(new BusinessHoursCalculator);
    $service->recordFirstResponse($ticket, $comment);

    $timer->refresh();

    expect($timer->stopped_at)->not->toBeNull();
});

test('recordFirstResponse does not set stopped_at for internal notes', function () {
    [$ticket, $timer, $agent] = makeTicketWithResponseTimer();

    $internalComment = TicketComment::create([
        'ticket_id'   => $ticket->id,
        'user_id'     => $agent->id,
        'body'        => 'Internal note',
        'is_internal' => true,
        'is_system'   => false,
    ]);

    $service = new SlaService(new BusinessHoursCalculator);
    $service->recordFirstResponse($ticket, $internalComment);

    $timer->refresh();

    expect($timer->stopped_at)->toBeNull();
});

test('subsequent calls to recordFirstResponse do not overwrite stopped_at', function () {
    [$ticket, $timer, $agent] = makeTicketWithResponseTimer();

    $firstComment = TicketComment::create([
        'ticket_id'   => $ticket->id,
        'user_id'     => $agent->id,
        'body'        => 'First response',
        'is_internal' => false,
        'is_system'   => false,
    ]);

    $service = new SlaService(new BusinessHoursCalculator);
    $service->recordFirstResponse($ticket, $firstComment);

    $timer->refresh();
    $originalStoppedAt = $timer->stopped_at;

    // Advance time and post a second comment
    $this->travel(5)->minutes();

    $secondComment = TicketComment::create([
        'ticket_id'   => $ticket->id,
        'user_id'     => $agent->id,
        'body'        => 'Follow-up response',
        'is_internal' => false,
        'is_system'   => false,
    ]);

    $service->recordFirstResponse($ticket, $secondComment);

    $timer->refresh();

    expect($timer->stopped_at->toIso8601String())
        ->toBe($originalStoppedAt->toIso8601String());
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 4: First Response Stops SLA Timer
 *
 * For 100 random open tickets with a response SlaTimer:
 *   - Post a first agent comment and call SlaService::recordFirstResponse
 *   - Assert stopped_at (first_response_at) is now set on the SlaTimer
 *   - Post 1–10 additional comments and call recordFirstResponse again each time
 *   - Assert stopped_at was NOT overwritten (still equals the original timestamp)
 */
it('sets stopped_at on first response and never overwrites it on subsequent comments', function () {
    [$ticket, $timer, $agent] = makeTicketWithResponseTimer();

    $service = new SlaService(new BusinessHoursCalculator);

    // ── First agent comment ───────────────────────────────────────────────────
    $firstComment = TicketComment::create([
        'ticket_id'   => $ticket->id,
        'user_id'     => $agent->id,
        'body'        => 'First response ' . bin2hex(random_bytes(4)),
        'is_internal' => false,
        'is_system'   => false,
    ]);

    $service->recordFirstResponse($ticket, $firstComment);

    $timer->refresh();

    expect($timer->stopped_at)->not->toBeNull(
        'stopped_at must be set after the first agent comment'
    );

    $originalStoppedAt = $timer->stopped_at->toIso8601String();

    // ── Subsequent comments (1–10) ────────────────────────────────────────────
    $extraComments = rand(1, 10);

    for ($i = 0; $i < $extraComments; $i++) {
        $this->travel(1)->minutes();

        $followUp = TicketComment::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $agent->id,
            'body'        => 'Follow-up ' . $i . ' ' . bin2hex(random_bytes(4)),
            'is_internal' => false,
            'is_system'   => false,
        ]);

        $service->recordFirstResponse($ticket, $followUp);

        $timer->refresh();

        expect($timer->stopped_at->toIso8601String())->toBe(
            $originalStoppedAt,
            "stopped_at was overwritten after follow-up comment #{$i}"
        );
    }
})->repeat(100);
