<?php

namespace App\Console\Commands;

use App\Actions\Email\EmailToTicketAction;
use App\Services\Email\EmailParser;
use App\Services\SettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollInboxCommand extends Command
{
    protected $signature = 'email:poll-inbox {--limit=20 : Max unseen emails to process}';

    protected $description = 'Poll IMAP inbox and convert unseen emails to tickets';

    public function handle(SettingService $settings, EmailParser $parser, EmailToTicketAction $action): int
    {
        if (! (bool) $settings->get('mail_inbound_enabled', false)) {
            $this->warn('Inbound mail is disabled in settings.');
            return self::SUCCESS;
        }

        if ($settings->get('mail_inbound_protocol', 'imap') !== 'imap') {
            $this->warn('Inbound protocol is not IMAP.');
            return self::SUCCESS;
        }

        if (! function_exists('imap_open')) {
            $this->error('PHP IMAP extension is not installed.');
            return self::FAILURE;
        }

        $host = (string) $settings->get('mail_inbound_host', '');
        $port = (int) $settings->get('mail_inbound_port', 993);
        $username = (string) $settings->get('mail_inbound_username', '');
        $password = (string) $settings->get('mail_inbound_password', '');
        $folder = (string) $settings->get('mail_inbound_folder', 'INBOX');
        $encryption = (string) $settings->get('mail_inbound_encryption', 'tls');

        if ($host === '' || $username === '' || $password === '') {
            $this->error('IMAP settings are incomplete. Set host/username/password first.');
            return self::FAILURE;
        }

        $flags = '/imap' . ($encryption === 'ssl' ? '/ssl' : ($encryption === 'tls' ? '/tls' : '/notls'));
        $mailbox = sprintf('{%s:%d%s}%s', $host, $port, $flags, $folder);

        $stream = @imap_open($mailbox, $username, $password);
        if ($stream === false) {
            $this->error('IMAP connect failed: ' . (imap_last_error() ?: 'unknown error'));
            return self::FAILURE;
        }

        try {
            $limit = max(1, (int) $this->option('limit'));
            $ids = imap_search($stream, 'UNSEEN') ?: [];
            $ids = array_slice($ids, 0, $limit);

            $processed = 0;
            foreach ($ids as $id) {
                $rawHeader = imap_fetchheader($stream, $id) ?: '';
                $rawBody = imap_body($stream, $id) ?: '';
                $rawEmail = $rawHeader . "\r\n" . $rawBody;

                try {
                    $parsed = $parser->parse($rawEmail);
                    $ticket = $action->execute($parsed);
                    imap_setflag_full($stream, (string) $id, '\\Seen');
                    $processed++;
                    Log::info('email:poll-inbox processed', ['ticket_id' => $ticket->id, 'imap_id' => $id]);
                } catch (\Throwable $e) {
                    Log::error('email:poll-inbox failed to process email', ['imap_id' => $id, 'error' => $e->getMessage()]);
                }
            }

            $this->info("Processed {$processed} email(s) from inbox.");
        } finally {
            imap_close($stream);
        }

        return self::SUCCESS;
    }
}
