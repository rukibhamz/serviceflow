<?php

namespace App\Mail;

use App\Models\SlaTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SlaBreachedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SlaTimer $timer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "SLA Breach: [{$this->timer->ticket->ulid}] {$this->timer->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.sla-breached',
        );
    }
}
