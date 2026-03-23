<?php

/**
 * Property 8: Outbound Email Threading
 * Validates: Requirements 4.3
 *
 * For any sequence of outbound mails on the same ticket, assert each mail after
 * the first carries `In-Reply-To` matching the previous `Message-ID`.
 */

use App\Actions\Tickets\CreateTicketAction;
use App\Mail\TicketCommentMail;
use App\Mail\TicketCreatedMail;
use App\Models\EmailThread;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Support\Facades\Event;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Extract the Message-ID value from a Mailable's headers.
 */
function getMessageId(\Illuminate\Mail\Mailable $mailable): string
{
    $headers = $mailable->headers();
    // Headers::messageId is stored as the messageId property
    return $headers->messageId ?? '';
}

/**
 * Extract the In-Reply-To value from a Mailable's headers.
 */
function getInReplyTo(\Illuminate\Mail\Mailable $mailable): ?string
{
    $headers = $mailable->headers();
    $text    = $headers->text ?? [];

    if (isset($text['In-Reply-To'])) {
        // Strip angle brackets if present: <id> → id
        return trim($text['In-Reply-To'], '<>');
    }

    return null;
}

/**
 * Create a ticket with a requester and return it.
 */
function makeTicketForThreading(): Ticket
{
    $requester = User::factory()->create();
    $action    = new CreateTicketAction;

    return $action->execute([
        'subject'  => 'Threading test ' . bin2hex(random_bytes(4)),
        'priority' => 'medium',
        'type'     => 'incident',
        'source'   => 'email',
    ], $requester);
}

/**
 * Create a non-internal comment on the given ticket.
 */
function makeCommentForThreading(Ticket $ticket): TicketComment
{
    $agent = User::factory()->create();

    return TicketComment::create([
        'ticket_id'   => $ticket->id,
        'user_id'     => $agent->id,
        'body'        => 'Comment body ' . bin2hex(random_bytes(4)),
        'is_internal' => false,
        'is_system'   => false,
    ]);
}

// ── Unit tests ────────────────────────────────────────────────────────────────

test('TicketCreatedMail has a Message-ID header', function () {
    Event::fake();

    $ticket = makeTicketForThreading();
    $mail   = new TicketCreatedMail($ticket);

    $messageId = getMessageId($mail);

    expect($messageId)->not->toBeEmpty()
        ->and($messageId)->toContain($ticket->ulid);
});

test('TicketCommentMail has In-Reply-To pointing to the ticket root Message-ID when no inbound thread exists', function () {
    Event::fake();

    $ticket  = makeTicketForThreading();
    $comment = makeCommentForThreading($ticket);
    $mail    = new TicketCommentMail($ticket, $comment);

    $expectedRootMessageId = "ticket-{$ticket->ulid}@serviceflow";
    $inReplyTo             = getInReplyTo($mail);

    expect($inReplyTo)->toBe($expectedRootMessageId);
});

test('TicketCommentMail Message-ID is unique per comment', function () {
    Event::fake();

    $ticket   = makeTicketForThreading();
    $comment1 = makeCommentForThreading($ticket);
    $comment2 = makeCommentForThreading($ticket);

    $mail1 = new TicketCommentMail($ticket, $comment1);
    $mail2 = new TicketCommentMail($ticket, $comment2);

    expect(getMessageId($mail1))->not->toBe(getMessageId($mail2));
});

test('two-mail sequence: comment In-Reply-To matches ticket created Message-ID', function () {
    Event::fake();

    $ticket  = makeTicketForThreading();
    $comment = makeCommentForThreading($ticket);

    $createdMail = new TicketCreatedMail($ticket);
    $commentMail = new TicketCommentMail($ticket, $comment);

    $firstMessageId = getMessageId($createdMail);
    $inReplyTo      = getInReplyTo($commentMail);

    expect($firstMessageId)->not->toBeEmpty()
        ->and($inReplyTo)->toBe($firstMessageId);
});

/**
 * Property 8: Outbound Email Threading
 *
 * For 100 random sequences of 2–10 outbound mails on the same ticket:
 *   - The first mail (TicketCreatedMail) has a non-empty Message-ID
 *   - Each subsequent mail (TicketCommentMail) has an In-Reply-To header
 *   - Each subsequent mail's In-Reply-To matches the previous mail's Message-ID
 */
it('maintains email thread chain: each outbound mail In-Reply-To matches the previous Message-ID', function () {
    Event::fake();

    // Random sequence length: 2–10 mails (1 ticket created + 1–9 comments)
    $commentCount = random_int(1, 9);

    $ticket = makeTicketForThreading();

    // ── First mail: TicketCreatedMail ────────────────────────────────────────
    $createdMail    = new TicketCreatedMail($ticket);
    $firstMessageId = getMessageId($createdMail);

    expect($firstMessageId)->not->toBeEmpty(
        "The first outbound mail must have a non-empty Message-ID"
    );

    // Record the outbound EmailThread for the ticket-created mail so subsequent
    // comment mails can thread off it.
    EmailThread::create([
        'ticket_id'   => $ticket->id,
        'message_id'  => $firstMessageId,
        'in_reply_to' => null,
        'from_address' => 'system@serviceflow',
        'direction'   => 'outbound',
    ]);

    $previousMessageId = $firstMessageId;

    // ── Subsequent mails: TicketCommentMail ──────────────────────────────────
    for ($i = 0; $i < $commentCount; $i++) {
        $comment     = makeCommentForThreading($ticket);
        $commentMail = new TicketCommentMail($ticket, $comment);

        $currentMessageId = getMessageId($commentMail);
        $inReplyTo        = getInReplyTo($commentMail);

        expect($inReplyTo)->not->toBeNull(
            "Mail #" . ($i + 1) . " must have an In-Reply-To header"
        )->and($inReplyTo)->toBe(
            $previousMessageId,
            "Mail #" . ($i + 1) . " In-Reply-To must match the previous mail's Message-ID"
        );

        // Record this outbound mail so the next comment can thread off it.
        EmailThread::create([
            'ticket_id'    => $ticket->id,
            'message_id'   => $currentMessageId,
            'in_reply_to'  => $previousMessageId,
            'from_address' => 'system@serviceflow',
            'direction'    => 'outbound',
        ]);

        $previousMessageId = $currentMessageId;
    }
})->repeat(100);
