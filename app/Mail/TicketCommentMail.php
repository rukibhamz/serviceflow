<?php

namespace App\Mail;

use App\Models\EmailThread;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class TicketCommentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketComment $comment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Re: [{$this->ticket->ulid}] {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.comment',
        );
    }

    public function headers(): Headers
    {
        $commentMessageId = "ticket-{$this->ticket->ulid}-comment-{$this->comment->id}@serviceflow";

        // Find the last outbound message_id for threading (chain outbound mails together)
        $lastOutbound = EmailThread::where('ticket_id', $this->ticket->id)
            ->where('direction', 'outbound')
            ->orderBy('id', 'desc')
            ->value('message_id');

        // Fall back to the ticket root message-id (from TicketCreatedMail) if no prior outbound exists
        $inReplyTo = $lastOutbound ?? "ticket-{$this->ticket->ulid}@serviceflow";

        return new Headers(
            messageId: $commentMessageId,
            references: [$inReplyTo],
            text: ['In-Reply-To' => "<{$inReplyTo}>"],
        );
    }
}
