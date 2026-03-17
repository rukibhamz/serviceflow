<?php

namespace App\Services\Email;

use App\DTOs\ParsedEmail;

class EmailParser
{
    public function parse(string $rawEmail): ParsedEmail
    {
        // Split headers from body on first \r\n\r\n or \n\n
        if (str_contains($rawEmail, "\r\n\r\n")) {
            [$headerSection, $body] = explode("\r\n\r\n", $rawEmail, 2);
        } elseif (str_contains($rawEmail, "\n\n")) {
            [$headerSection, $body] = explode("\n\n", $rawEmail, 2);
        } else {
            $headerSection = $rawEmail;
            $body = '';
        }

        $headers = $this->parseHeaders($headerSection);

        $messageId = $this->stripAngleBrackets($headers['message-id'] ?? '');
        $inReplyTo = $this->stripAngleBrackets($headers['in-reply-to'] ?? null);
        $subject = $this->decodeHeader($headers['subject'] ?? '');
        [$fromAddress, $fromName] = $this->parseFrom($headers['from'] ?? '');

        // Decode body based on Content-Transfer-Encoding
        $encoding = strtolower(trim($headers['content-transfer-encoding'] ?? ''));
        $body = $this->decodeBody($body, $encoding);

        // Strip HTML tags if content-type is text/html
        $contentType = strtolower($headers['content-type'] ?? '');
        if (str_contains($contentType, 'text/html')) {
            $body = strip_tags($body);
        }

        $body = trim($body);

        return new ParsedEmail(
            messageId: $messageId,
            inReplyTo: $inReplyTo ?: null,
            fromAddress: $fromAddress,
            fromName: $fromName ?: null,
            subject: $subject,
            body: $body,
            attachments: [],
            rawHeaders: $headers,
        );
    }

    private function parseHeaders(string $headerSection): array
    {
        // Unfold multi-line headers (RFC 2822 folding)
        $headerSection = preg_replace("/\r\n([ \t])/", ' $1', $headerSection);
        $headerSection = preg_replace("/\n([ \t])/", ' $1', $headerSection);

        $headers = [];
        $lines = preg_split("/\r?\n/", $headerSection);

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }

        return $headers;
    }

    private function stripAngleBrackets(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim($value);

        if (str_starts_with($value, '<') && str_ends_with($value, '>')) {
            return substr($value, 1, -1);
        }

        // Handle cases where angle brackets are embedded
        if (preg_match('/<([^>]+)>/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }

    private function parseFrom(string $from): array
    {
        $from = trim($from);

        // Format: "Name" <email> or Name <email>
        if (preg_match('/^"?([^"<]*)"?\s*<([^>]+)>$/', $from, $matches)) {
            $name = trim($matches[1]);
            $email = trim($matches[2]);
            return [$email, $name ?: null];
        }

        // Plain email address
        return [$from, null];
    }

    private function decodeBody(string $body, string $encoding): string
    {
        return match ($encoding) {
            'quoted-printable' => quoted_printable_decode($body),
            'base64'           => base64_decode(str_replace(["\r", "\n"], '', $body)),
            default            => $body,
        };
    }

    private function decodeHeader(string $value): string
    {
        // Decode RFC 2047 encoded words: =?charset?encoding?text?=
        return preg_replace_callback(
            '/=\?([^?]+)\?([BbQq])\?([^?]*)\?=/',
            function (array $matches) {
                $charset = $matches[1];
                $encoding = strtoupper($matches[2]);
                $text = $matches[3];

                $decoded = match ($encoding) {
                    'B' => base64_decode($text),
                    'Q' => quoted_printable_decode(str_replace('_', ' ', $text)),
                    default => $text,
                };

                if (strtolower($charset) !== 'utf-8') {
                    $decoded = mb_convert_encoding($decoded, 'UTF-8', $charset);
                }

                return $decoded;
            },
            $value
        ) ?? $value;
    }
}
