<?php

namespace App\Listeners;

use App\Events\SlaBreached;
use App\Services\Email\TicketMailer;

class SendSlaBreachedMail
{
    public function __construct(private readonly TicketMailer $mailer) {}

    public function handle(SlaBreached $event): void
    {
        $this->mailer->sendSlaBreached($event->timer);
    }
}
