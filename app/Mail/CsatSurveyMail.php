<?php

namespace App\Mail;

use App\Models\CsatSurvey;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CsatSurveyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly CsatSurvey $survey,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "How did we do? — [{$this->ticket->ulid}] {$this->ticket->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.csat.survey');
    }
}
