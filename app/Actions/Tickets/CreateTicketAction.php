<?php

namespace App\Actions\Tickets;

use App\Events\TicketCreated;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class CreateTicketAction
{
    public function execute(array $data, User $requester): Ticket
    {
        $validator = Validator::make($data, [
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['required', 'string', 'in:low,medium,high,critical'],
            'type'        => ['required', 'string', 'in:incident,service_request,problem,change'],
            'source'      => ['nullable', 'string', 'in:web,email,api'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        $ticket = Ticket::create([
            'ulid'         => (string) Str::ulid(),
            'subject'      => $validated['subject'],
            'description'  => $validated['description'] ?? null,
            'priority'     => $validated['priority'],
            'type'         => $validated['type'],
            'source'       => $validated['source'] ?? 'web',
            'status'       => 'open',
            'requester_id' => $requester->id,
        ]);

        TicketCreated::dispatch($ticket);

        return $ticket;
    }
}
