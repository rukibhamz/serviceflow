<?php

/**
 * Property 11: CSAT Survey Uniqueness and Idempotence
 * Validates: Requirements 6.3
 *
 * Submitting a CSAT rating multiple times for the same ticket/requester must:
 *   - Result in exactly one csat_surveys record per ticket/requester pair
 *   - Reflect the last valid rating submitted
 *   - Never throw on repeated calls to sendSurvey (idempotent survey creation)
 */

use App\Mail\CsatSurveyMail;
use App\Models\CsatSurvey;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Portal\CsatService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    Mail::fake();
});

// ── Unit tests ────────────────────────────────────────────────────────────────

test('sendSurvey creates exactly one record per ticket/requester', function () {
    $requester = User::factory()->create();
    $ticket    = Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Test ticket',
        'priority'     => 'medium',
        'type'         => 'incident',
        'status'       => 'closed',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $service = new CsatService();

    $service->sendSurvey($ticket);
    $service->sendSurvey($ticket); // second call — must be idempotent

    expect(CsatSurvey::where('ticket_id', $ticket->id)->count())->toBe(1);
});

test('sendSurvey only queues one email even when called multiple times', function () {
    $requester = User::factory()->create();
    $ticket    = Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Email dedup ticket',
        'priority'     => 'low',
        'type'         => 'incident',
        'status'       => 'closed',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $service = new CsatService();
    $service->sendSurvey($ticket);
    $service->sendSurvey($ticket);

    Mail::assertQueued(CsatSurveyMail::class, 1);
});

test('recordRating updates the rating on the existing survey', function () {
    $requester = User::factory()->create();
    $ticket    = Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Rating update ticket',
        'priority'     => 'high',
        'type'         => 'incident',
        'status'       => 'closed',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $survey = CsatSurvey::create([
        'ticket_id'    => $ticket->id,
        'requester_id' => $requester->id,
        'token'        => Str::random(40),
        'sent_at'      => now(),
    ]);

    $service = new CsatService();
    $service->recordRating($survey->token, 3);
    $service->recordRating($survey->token, 5, 'Great support!');

    $updated = $survey->fresh();

    expect($updated->rating)->toBe(5)
        ->and($updated->comment)->toBe('Great support!')
        ->and(CsatSurvey::where('ticket_id', $ticket->id)->count())->toBe(1);
});

test('recordRating stamps responded_at only on first response', function () {
    $requester = User::factory()->create();
    $ticket    = Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Timestamp test ticket',
        'priority'     => 'low',
        'type'         => 'incident',
        'status'       => 'closed',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $survey = CsatSurvey::create([
        'ticket_id'    => $ticket->id,
        'requester_id' => $requester->id,
        'token'        => Str::random(40),
        'sent_at'      => now(),
    ]);

    $service = new CsatService();
    $service->recordRating($survey->token, 4);
    $firstStamp = $survey->fresh()->responded_at;

    // Advance time and submit again
    $this->travel(10)->minutes();
    $service->recordRating($survey->token, 2);

    expect($survey->fresh()->responded_at->eq($firstStamp))->toBeTrue();
});

test('recordRating clamps rating to 1–5 range', function () {
    $requester = User::factory()->create();
    $ticket    = Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Clamp test ticket',
        'priority'     => 'medium',
        'type'         => 'incident',
        'status'       => 'closed',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $survey = CsatSurvey::create([
        'ticket_id'    => $ticket->id,
        'requester_id' => $requester->id,
        'token'        => Str::random(40),
        'sent_at'      => now(),
    ]);

    $service = new CsatService();
    $service->recordRating($survey->token, 0);   // below min
    expect($survey->fresh()->rating)->toBe(1);

    $service->recordRating($survey->token, 99);  // above max
    expect($survey->fresh()->rating)->toBe(5);
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 11: CSAT Survey Uniqueness and Idempotence
 *
 * For 100 random scenarios:
 *   - Create a ticket with a random requester
 *   - Call sendSurvey N times (1–5 random calls)
 *   - Assert exactly one CsatSurvey record exists for the ticket/requester pair
 *   - Submit M random ratings (1–5 random submissions)
 *   - Assert exactly one record still exists and rating equals the last submitted value
 */
it('maintains exactly one survey record and reflects the last rating for any number of submissions', function () {
    $requester = User::factory()->create();
    $ticket    = Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Property test ' . bin2hex(random_bytes(4)),
        'priority'     => collect(['low', 'medium', 'high', 'critical'])->random(),
        'type'         => 'incident',
        'status'       => 'closed',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);

    $service = new CsatService();

    // Call sendSurvey between 1 and 5 times
    $sendCount = random_int(1, 5);
    for ($i = 0; $i < $sendCount; $i++) {
        $service->sendSurvey($ticket);
    }

    // Exactly one survey record must exist
    expect(CsatSurvey::where('ticket_id', $ticket->id)->where('requester_id', $requester->id)->count())
        ->toBe(1, "Expected 1 survey after {$sendCount} sendSurvey calls, got more");

    $survey = CsatSurvey::where('ticket_id', $ticket->id)->first();

    // Submit between 1 and 5 ratings
    $ratingCount = random_int(1, 5);
    $lastRating  = null;
    for ($j = 0; $j < $ratingCount; $j++) {
        $lastRating = random_int(1, 5);
        $service->recordRating($survey->token, $lastRating);
    }

    $fresh = $survey->fresh();

    // Still exactly one record
    expect(CsatSurvey::where('ticket_id', $ticket->id)->count())
        ->toBe(1, 'Expected exactly 1 survey record after multiple rating submissions');

    // Rating reflects the last submission
    expect($fresh->rating)->toBe($lastRating,
        "Expected rating {$lastRating} after {$ratingCount} submissions, got {$fresh->rating}");
})->repeat(100);
