<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Services\Email\TicketMailer;

class SendTicketCreatedMail
{
    public function __construct(private readonly TicketMailer $mailer) {}

    public function handle(TicketCreated $event): void
    {
        $this->mailer->sendTicketCreated($event->ticket);
    }
}
