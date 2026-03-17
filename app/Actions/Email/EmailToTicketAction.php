<?php

namespace App\Actions\Email;

use App\Actions\Tickets\CreateTicketAction;
use App\DTOs\ParsedEmail;
use App\Models\EmailThread;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmailToTicketAction
{
    public function __construct(private readonly CreateTicketAction $createTicketAction) {}

    public function execute(ParsedEmail $email): Ticket
    {
        // 1. Look up EmailThread by in_reply_to — if found, append a comment
        if ($email->inReplyTo) {
            $thread = EmailThread::where('message_id', $email->inReplyTo)->first();

            if ($thread) {
                $ticket = $thread->ticket;

                // Find or create the user for the sender
                $user = $this->findOrCreateUser($email->fromAddress, $email->fromName);

                // Append a comment to the existing ticket
                TicketComment::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $user->id,
                    'body'        => $email->body,
                    'is_internal' => false,
                    'is_system'   => false,
                ]);

                // Record this inbound message as a new thread entry
                $this->createEmailThread($ticket, $email);

                return $ticket;
            }
        }

        // 2. No thread found — find or create user and create a new ticket
        $user = $this->findOrCreateUser($email->fromAddress, $email->fromName);

        $ticket = $this->createTicketAction->execute([
            'subject'     => $email->subject ?: '(No Subject)',
            'description' => $email->body,
            'priority'    => 'medium',
            'type'        => 'incident',
            'source'      => 'email',
        ], $user);

        // 3. Create EmailThread record for this inbound message
        $this->createEmailThread($ticket, $email);

        return $ticket;
    }

    private function findOrCreateUser(string $email, ?string $name): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name ?? $email,
                'password' => Hash::make(Str::random(32)),
                'role'     => 'end_user',
            ]
        );
    }

    private function createEmailThread(Ticket $ticket, ParsedEmail $email): EmailThread
    {
        return EmailThread::create([
            'ticket_id'    => $ticket->id,
            'message_id'   => $email->messageId,
            'in_reply_to'  => $email->inReplyTo,
            'from_address' => $email->fromAddress,
            'from_name'    => $email->fromName,
            'direction'    => 'inbound',
            'raw_headers'  => json_encode($email->rawHeaders),
        ]);
    }
}
