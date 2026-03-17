<?php

namespace App\DTOs;

readonly class ParsedEmail
{
    public function __construct(
        public string $messageId,
        public ?string $inReplyTo,
        public string $fromAddress,
        public ?string $fromName,
        public string $subject,
        public string $body,
        public array $attachments = [], // array of ['name' => string, 'mime' => string, 'content' => string (base64)]
        public array $rawHeaders = [],
    ) {}
}
