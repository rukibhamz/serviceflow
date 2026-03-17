<?php

namespace App\Services\Email;

use App\Mail\SlaBreachedMail;
use App\Mail\TicketCommentMail;
use App\Mail\TicketCreatedMail;
use App\Models\SlaTimer;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Support\Facades\Mail;

class TicketMailer
{
    public function sendTicketCreated(Ticket $ticket): void
    {
        $requester = $ticket->requester;

        if (! $requester?->email) {
            return;
        }

        Mail::to($requester->email)->queue(new TicketCreatedMail($ticket));
    }

    public function sendCommentAdded(Ticket $ticket, TicketComment $comment): void
    {
        // Skip internal comments
        if ($comment->is_internal) {
            return;
        }

        $recipients = collect();

        // Add requester
        if ($ticket->requester?->email) {
            $recipients->push($ticket->requester->email);
        }

        // Add watchers
        $ticket->watchers->each(function ($watcher) use ($recipients) {
            if ($watcher->email) {
                $recipients->push($watcher->email);
            }
        });

        $recipients->unique()->each(function (string $email) use ($ticket, $comment) {
            Mail::to($email)->queue(new TicketCommentMail($ticket, $comment));
        });
    }

    public function sendSlaBreached(SlaTimer $timer): void
    {
        $ticket = $timer->ticket;
        $recipient = null;

        // Mail to assignee if set
        if ($ticket->assignee?->email) {
            $recipient = $ticket->assignee->email;
        } elseif ($ticket->team) {
            // Fall back to team manager (first manager in team)
            $manager = $ticket->team->users()
                ->where('role', 'manager')
                ->first();

            if ($manager?->email) {
                $recipient = $manager->email;
            }
        }

        if (! $recipient) {
            return;
        }

        Mail::to($recipient)->queue(new SlaBreachedMail($timer));
    }
}
