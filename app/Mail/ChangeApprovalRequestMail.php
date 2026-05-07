<?php

namespace App\Mail;

use App\Models\ChangeApprover;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChangeApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ChangeApprover $approver,
        public readonly string $brandName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[CAB Approval Required] {$this->approver->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.change.approval-request');
    }
}
