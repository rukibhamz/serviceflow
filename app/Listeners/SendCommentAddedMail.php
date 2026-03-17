<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use App\Services\Email\TicketMailer;

class SendCommentAddedMail
{
    public function __construct(private readonly TicketMailer $mailer) {}

    public function handle(CommentAdded $event): void
    {
        $this->mailer->sendCommentAdded($event->comment->ticket, $event->comment);
    }
}
