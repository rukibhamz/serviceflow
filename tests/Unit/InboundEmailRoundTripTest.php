<?php

/**
 * Property 7: Inbound Email Round-Trip
 * Validates: Requirements 4.1, 4.2
 *
 * Generate arbitrary valid RFC 2822 email strings; assert parsing produces correct
 * DTO fields and `EmailToTicketAction` creates or threads correctly.
 */

use App\Actions\Email\EmailToTicketAction;
use App\Actions\Tickets\CreateTicketAction;
use App\DTOs\ParsedEmail;
use App\Models\EmailThread;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Support\Facades\Event;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => Event::fake());

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Build a minimal RFC 2822 email string from the given parts.
 */
function buildRawEmail(
    string $messageId,
    string $fromAddress,
    ?string $fromName,
    string $subject,
    string $body,
    ?string $inReplyTo = null,
): string {
    $from = $fromName
        ? "\"{$fromName}\" <{$fromAddress}>"
        : $fromAddress;

    $headers = "Message-ID: <{$messageId}>\r\n"
        . "From: {$from}\r\n"
        . "Subject: {$subject}\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";

    if ($inReplyTo !== null) {
        $headers .= "In-Reply-To: <{$inReplyTo}>\r\n";
    }

    return $headers . "\r\n" . $body;
}

/**
 * Generate a random printable ASCII string of the given length.
 */
function randomString(int $length = 8): string
{
    $chars  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $result;
}

/**
 * Generate a random valid-looking email address.
 */
function randomEmail(): string
{
    return randomString(6) . '@' . randomString(5) . '.example';
}

/**
 * Generate a random RFC 2822 message-id (without angle brackets).
 */
function randomMessageId(): string
{
    return randomString(12) . '.' . randomString(8) . '@mail.example';
}

// ── Unit tests ────────────────────────────────────────────────────────────────

test('EmailParser extracts correct fields from a plain RFC 2822 email', function () {
    $parser = new \App\Services\Email\EmailParser;

    $raw = buildRawEmail(
        messageId: 'abc123.def456@mail.example',
        fromAddress: 'alice@example.com',
        fromName: 'Alice Smith',
        subject: 'Hello World',
        body: 'This is the body.',
    );

    $dto = $parser->parse($raw);

    expect($dto)->toBeInstanceOf(ParsedEmail::class)
        ->and($dto->messageId)->toBe('abc123.def456@mail.example')
        ->and($dto->fromAddress)->toBe('alice@example.com')
        ->and($dto->fromName)->toBe('Alice Smith')
        ->and($dto->subject)->toBe('Hello World')
        ->and($dto->body)->toBe('This is the body.')
        ->and($dto->inReplyTo)->toBeNull();
});

test('EmailParser captures in-reply-to header', function () {
    $parser = new \App\Services\Email\EmailParser;

    $raw = buildRawEmail(
        messageId: 'reply001@mail.example',
        fromAddress: 'bob@example.com',
        fromName: null,
        subject: 'Re: Hello',
        body: 'Reply body.',
        inReplyTo: 'original001@mail.example',
    );

    $dto = $parser->parse($raw);

    expect($dto->inReplyTo)->toBe('original001@mail.example');
});

test('EmailToTicketAction creates a new ticket when no matching thread exists', function () {
    $action = new EmailToTicketAction(new CreateTicketAction);

    $dto = new ParsedEmail(
        messageId: 'new001@mail.example',
        inReplyTo: null,
        fromAddress: 'user@example.com',
        fromName: 'Test User',
        subject: 'New ticket subject',
        body: 'Ticket body text.',
    );

    $ticketsBefore = Ticket::count();
    $ticket        = $action->execute($dto);

    expect(Ticket::count())->toBe($ticketsBefore + 1)
        ->and($ticket)->toBeInstanceOf(Ticket::class)
        ->and($ticket->subject)->toBe('New ticket subject');

    $this->assertDatabaseHas('email_threads', [
        'ticket_id'  => $ticket->id,
        'message_id' => 'new001@mail.example',
        'direction'  => 'inbound',
    ]);
});

test('EmailToTicketAction appends a comment when in-reply-to matches an existing thread', function () {
    $action = new EmailToTicketAction(new CreateTicketAction);

    // Create the original ticket + thread
    $original = new ParsedEmail(
        messageId: 'orig001@mail.example',
        inReplyTo: null,
        fromAddress: 'user@example.com',
        fromName: 'User',
        subject: 'Original ticket',
        body: 'Original body.',
    );
    $ticket = $action->execute($original);

    $commentsBefore = TicketComment::where('ticket_id', $ticket->id)->count();
    $ticketsBefore  = Ticket::count();

    // Reply to the original
    $reply = new ParsedEmail(
        messageId: 'reply001@mail.example',
        inReplyTo: 'orig001@mail.example',
        fromAddress: 'user@example.com',
        fromName: 'User',
        subject: 'Re: Original ticket',
        body: 'Reply body.',
    );
    $returnedTicket = $action->execute($reply);

    expect($returnedTicket->id)->toBe($ticket->id)
        ->and(Ticket::count())->toBe($ticketsBefore, 'No new ticket should be created for a reply')
        ->and(TicketComment::where('ticket_id', $ticket->id)->count())->toBe($commentsBefore + 1);
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 7: Inbound Email Round-Trip
 *
 * For 100 random RFC 2822 email strings:
 *   - Assert EmailParser produces a ParsedEmail DTO with correct from, subject,
 *     body, and message-id fields
 *   - Assert EmailToTicketAction creates a new ticket when no matching thread exists
 *   - Assert EmailToTicketAction appends a comment (not a new ticket) when
 *     in-reply-to matches an existing EmailThread.message_id
 *   - Assert an EmailThread record is created for every inbound message
 */
it('parses RFC 2822 emails correctly and creates or threads tickets', function () {
    $parser = new \App\Services\Email\EmailParser;
    $action = new EmailToTicketAction(new CreateTicketAction);

    // ── Generate random email parts ──────────────────────────────────────────
    $messageId   = randomMessageId();
    $fromAddress = randomEmail();
    $fromName    = (bool) random_int(0, 1) ? randomString(random_int(3, 12)) : null;
    $subject     = 'Subject ' . randomString(random_int(4, 20));
    $body        = 'Body ' . randomString(random_int(10, 80));

    // ── 1. Parse the raw email and assert DTO fields ─────────────────────────
    $raw = buildRawEmail($messageId, $fromAddress, $fromName, $subject, $body);
    $dto = $parser->parse($raw);

    expect($dto)->toBeInstanceOf(ParsedEmail::class);
    expect($dto->messageId)->toBe($messageId, "messageId should match the Message-ID header");
    expect($dto->fromAddress)->toBe($fromAddress, "fromAddress should match the From header");
    expect($dto->subject)->toBe($subject, "subject should match the Subject header");
    expect($dto->body)->toBe($body, "body should match the email body");
    expect($dto->inReplyTo)->toBeNull("inReplyTo should be null when no In-Reply-To header is present");

    if ($fromName !== null) {
        expect($dto->fromName)->toBe($fromName, "fromName should match the display name in the From header");
    }

    // ── 2. New email → new ticket + EmailThread ──────────────────────────────
    $ticketsBefore  = Ticket::count();
    $threadsBefore  = EmailThread::count();

    $ticket = $action->execute($dto);

    expect(Ticket::count())->toBe($ticketsBefore + 1, "A new ticket should be created for a fresh email");
    expect(EmailThread::count())->toBe($threadsBefore + 1, "An EmailThread should be created for every inbound message");

    $this->assertDatabaseHas('email_threads', [
        'ticket_id'    => $ticket->id,
        'message_id'   => $messageId,
        'from_address' => $fromAddress,
        'direction'    => 'inbound',
    ]);

    // ── 3. Reply email → comment appended, no new ticket ────────────────────
    $replyMessageId = randomMessageId();
    $replyBody      = 'Reply ' . randomString(random_int(10, 60));

    $replyRaw = buildRawEmail(
        messageId: $replyMessageId,
        fromAddress: $fromAddress,
        fromName: $fromName,
        subject: 'Re: ' . $subject,
        body: $replyBody,
        inReplyTo: $messageId,
    );

    $replyDto = $parser->parse($replyRaw);

    expect($replyDto->inReplyTo)->toBe($messageId, "Parsed in-reply-to should match the original message-id");

    $ticketsAfterReply  = Ticket::count();
    $commentsBefore     = TicketComment::where('ticket_id', $ticket->id)->count();
    $threadsAfterFirst  = EmailThread::count();

    $returnedTicket = $action->execute($replyDto);

    expect($returnedTicket->id)->toBe($ticket->id, "Reply should thread onto the existing ticket");
    expect(Ticket::count())->toBe($ticketsAfterReply, "No new ticket should be created for a reply");
    expect(TicketComment::where('ticket_id', $ticket->id)->count())
        ->toBe($commentsBefore + 1, "A comment should be appended to the existing ticket");
    expect(EmailThread::count())
        ->toBe($threadsAfterFirst + 1, "An EmailThread should be created for the reply message too");
})->repeat(100);
