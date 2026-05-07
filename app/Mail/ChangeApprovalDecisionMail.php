<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChangeApprovalDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly string $decision,   // 'approved' or 'rejected'
        public readonly string $approverName,
        public readonly ?string $comment,
        public readonly string $brandName,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->decision === 'approved' ? '✅ Approved' : '❌ Rejected';
        return new Envelope(
            subject: "[Change {$label}] {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.change.approval-decision');
    }
}
