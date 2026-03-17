<?php

namespace App\Console\Commands;

use App\Actions\Email\EmailToTicketAction;
use App\Services\Email\EmailParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IngestEmailCommand extends Command
{
    protected $signature = 'email:ingest';

    protected $description = 'Ingest a raw email from stdin and create or update a ticket';

    public function handle(EmailParser $parser, EmailToTicketAction $action): int
    {
        try {
            $stdin = fopen('php://stdin', 'r');
            $rawEmail = '';

            while (! feof($stdin)) {
                $rawEmail .= fread($stdin, 8192);
            }

            fclose($stdin);

            if (empty(trim($rawEmail))) {
                $this->error('No email content received on stdin.');
                Log::error('email:ingest — empty stdin');
                return self::FAILURE;
            }

            $parsed = $parser->parse($rawEmail);
            $ticket = $action->execute($parsed);

            Log::info('email:ingest — ticket created/updated', ['ticket_id' => $ticket->id, 'ulid' => $ticket->ulid]);
            $this->info("Ticket #{$ticket->ulid} created/updated successfully.");

        } catch (\Throwable $e) {
            // Never bounce email — always exit 0
            Log::error('email:ingest — exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return self::SUCCESS;
    }
}
