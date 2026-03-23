<?php

/**
 * Property 5: SLA Breach Detection
 * Validates: Requirements 3.4
 *
 * Generate timers where elapsed time exceeds threshold; assert `breached` flag is set.
 * Generate timers within threshold and assert not breached.
 */

use App\Models\SlaPolicy;
use App\Models\SlaTimer;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Sla\BusinessHoursCalculator;
use App\Services\Sla\SlaService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => Event::fake());

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeTimerWithDueAt(\Carbon\Carbon $dueAt): SlaTimer
{
    $policy = SlaPolicy::create([
        'name'               => 'Breach Test SLA ' . bin2hex(random_bytes(4)),
        'priority'           => 'medium',
        'response_minutes'   => 120,
        'resolution_minutes' => 480,
        'is_active'          => true,
        'is_default'         => false,
    ]);

    $requester = User::factory()->create();

    $ticket = Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Breach test ticket ' . bin2hex(random_bytes(4)),
        'priority'     => 'medium',
        'type'         => 'incident',
        'status'       => 'open',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    return SlaTimer::create([
        'ticket_id'     => $ticket->id,
        'sla_policy_id' => $policy->id,
        'type'          => 'resolution',
        'due_at'        => $dueAt,
        'breached'      => false,
    ]);
}

// ── Unit tests ────────────────────────────────────────────────────────────────

test('checkBreach returns true and sets breached=true when due_at is in the past', function () {
    $timer = makeTimerWithDueAt(now()->subMinutes(30));

    $service = new SlaService(new BusinessHoursCalculator);
    $result  = $service->checkBreach($timer);

    expect($result)->toBeTrue();

    $timer->refresh();
    expect($timer->breached)->toBeTrue();
});

test('checkBreach returns false and leaves breached=false when due_at is in the future', function () {
    $timer = makeTimerWithDueAt(now()->addMinutes(30));

    $service = new SlaService(new BusinessHoursCalculator);
    $result  = $service->checkBreach($timer);

    expect($result)->toBeFalse();

    $timer->refresh();
    expect($timer->breached)->toBeFalse();
});

test('checkBreach returns false when timer is already stopped', function () {
    $timer             = makeTimerWithDueAt(now()->subMinutes(10));
    $timer->stopped_at = now()->subMinutes(5);
    $timer->save();

    $service = new SlaService(new BusinessHoursCalculator);
    $result  = $service->checkBreach($timer);

    expect($result)->toBeFalse();

    $timer->refresh();
    expect($timer->breached)->toBeFalse();
});

test('checkBreach returns false when timer is already marked breached', function () {
    $timer          = makeTimerWithDueAt(now()->subMinutes(10));
    $timer->breached = true;
    $timer->save();

    $service = new SlaService(new BusinessHoursCalculator);
    $result  = $service->checkBreach($timer);

    expect($result)->toBeFalse();
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 5: SLA Breach Detection
 *
 * For 100 random SlaTimers:
 *   - Randomly generate a time offset (1–3600 seconds) in the past or future
 *   - Call SlaService::checkBreach on the timer
 *   - Assert breached=true when due_at is in the past (elapsed > threshold)
 *   - Assert breached=false when due_at is in the future (within threshold)
 */
it('sets breached=true for overdue timers and breached=false for timers within threshold', function () {
    $service = new SlaService(new BusinessHoursCalculator);

    // Random offset: 1–3600 seconds; randomly past or future
    $offsetSeconds = rand(1, 3600);
    $isPast        = (bool) rand(0, 1);

    $dueAt = $isPast
        ? now()->subSeconds($offsetSeconds)
        : now()->addSeconds($offsetSeconds);

    $timer = makeTimerWithDueAt($dueAt);

    $result = $service->checkBreach($timer);

    $timer->refresh();

    if ($isPast) {
        expect($result)->toBeTrue(
            "checkBreach should return true for a timer due {$offsetSeconds}s ago"
        );
        expect($timer->breached)->toBeTrue(
            "breached flag should be true for a timer due {$offsetSeconds}s ago"
        );
    } else {
        expect($result)->toBeFalse(
            "checkBreach should return false for a timer due in {$offsetSeconds}s"
        );
        expect($timer->breached)->toBeFalse(
            "breached flag should remain false for a timer due in {$offsetSeconds}s"
        );
    }
})->repeat(100);
