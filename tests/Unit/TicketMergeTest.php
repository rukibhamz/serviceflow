<?php

/**
 * Property 12: Ticket Merge Completeness
 * Validates: Requirements 2.6
 *
 * Generate two tickets with random comment counts (0–10 each); after merge assert:
 *   - All source comments now belong to the target ticket
 *   - Source ticket status is `closed`
 *   - Source ticket `merged_into_id` equals target ticket id
 *   - Target ticket comment count equals original source + target comment counts
 *     (plus the one system comment added by MergeTicketsAction)
 */

use App\Actions\Tickets\MergeTicketsAction;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\Tickets\TicketStatusMachine;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

// Fake all events to prevent activity_log writes (LogsActivity uses events internally)
beforeEach(fn () => Event::fake());

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeMergeTicket(User $requester): Ticket
{
    return Ticket::create([
        'ulid'         => Str::ulid(),
        'subject'      => 'Ticket ' . Str::random(8),
        'status'       => 'open',
        'priority'     => 'medium',
        'type'         => 'incident',
        'source'       => 'web',
        'requester_id' => $requester->id,
    ]);
}

function addComments(Ticket $ticket, int $count, User $author): void
{
    for ($i = 0; $i < $count; $i++) {
        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $author->id,
            'body'      => 'Comment ' . Str::random(12),
            'is_system' => false,
        ]);
    }
}

// ── Unit tests ────────────────────────────────────────────────────────────────

test('merge moves all source comments to target ticket', function () {
    $user   = User::factory()->create();
    $target = makeMergeTicket($user);
    $source = makeMergeTicket($user);

    addComments($target, 2, $user);
    addComments($source, 3, $user);

    $action = new MergeTicketsAction(new TicketStatusMachine);
    $action->execute($target, $source);

    // All original source comment ticket_ids should now point to target
    $sourceCommentIds = TicketComment::where('ticket_id', $target->id)
        ->where('is_system', false)
        ->count();

    expect($sourceCommentIds)->toBe(5); // 2 target + 3 source
});

test('merge sets source status to closed', function () {
    $user   = User::factory()->create();
    $target = makeMergeTicket($user);
    $source = makeMergeTicket($user);

    $action = new MergeTicketsAction(new TicketStatusMachine);
    $action->execute($target, $source);

    $source->refresh();
    expect($source->status)->toBe('closed');
});

test('merge sets merged_into_id on source to target id', function () {
    $user   = User::factory()->create();
    $target = makeMergeTicket($user);
    $source = makeMergeTicket($user);

    $action = new MergeTicketsAction(new TicketStatusMachine);
    $action->execute($target, $source);

    $source->refresh();
    expect($source->merged_into_id)->toBe($target->id);
});

test('merge with zero comments on both tickets still closes source correctly', function () {
    $user   = User::factory()->create();
    $target = makeMergeTicket($user);
    $source = makeMergeTicket($user);

    $action = new MergeTicketsAction(new TicketStatusMachine);
    $action->execute($target, $source);

    $source->refresh();
    expect($source->status)->toBe('closed')
        ->and($source->merged_into_id)->toBe($target->id);
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 12: Ticket Merge Completeness
 *
 * For 100 random pairs of (sourceCommentCount, targetCommentCount) in [0..10]:
 *   - All source comments are reassigned to target
 *   - Source status is `closed`
 *   - Source `merged_into_id` equals target id
 *   - Target comment count = sourceCount + targetCount + 1 (system comment)
 */
it('satisfies merge completeness for arbitrary comment counts', function () {
    $user   = User::factory()->create();
    $action = new MergeTicketsAction(new TicketStatusMachine);

    $sourceCount = random_int(0, 10);
    $targetCount = random_int(0, 10);

    $target = makeMergeTicket($user);
    $source = makeMergeTicket($user);

    addComments($target, $targetCount, $user);
    addComments($source, $sourceCount, $user);

    $action->execute($target, $source);

    $source->refresh();
    $target->refresh();

    // Source status must be closed
    expect($source->status)->toBe('closed');

    // merged_into_id must point to target
    expect($source->merged_into_id)->toBe($target->id);

    // All comments (user + system) on target = sourceCount + targetCount + 1 system comment
    $totalTargetComments = TicketComment::where('ticket_id', $target->id)->count();
    expect($totalTargetComments)->toBe($sourceCount + $targetCount + 1);

    // No user comments remain on source
    $remainingSourceUserComments = TicketComment::where('ticket_id', $source->id)
        ->where('is_system', false)
        ->count();
    expect($remainingSourceUserComments)->toBe(0);
})->repeat(100);
