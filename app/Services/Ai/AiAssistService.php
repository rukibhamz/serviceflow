<?php

namespace App\Services\Ai;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Assist service — integrates with any OpenAI-compatible LLM API.
 *
 * Configuration (in .env):
 *   AI_ASSIST_ENDPOINT=https://api.openai.com/v1
 *   AI_ASSIST_API_KEY=sk-...
 *   AI_ASSIST_MODEL=gpt-4o-mini
 */
class AiAssistService
{
    private string $endpoint;
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->endpoint = rtrim(config('ai.endpoint', 'https://api.openai.com/v1'), '/');
        $this->apiKey   = config('ai.api_key', '');
        $this->model    = config('ai.model', 'gpt-4o-mini');
    }

    /**
     * Summarise a ticket's subject + description + recent comments into 2–3 sentences.
     */
    public function summarise(Ticket $ticket): string
    {
        $comments = $ticket->comments()
            ->where('is_internal', false)
            ->latest()
            ->take(5)
            ->pluck('body')
            ->implode("\n---\n");

        $prompt = <<<PROMPT
Summarise the following support ticket in 2-3 sentences. Be concise and factual.

Subject: {$ticket->subject}
Description: {$ticket->description}
Recent comments:
{$comments}
PROMPT;

        return $this->chat($prompt);
    }

    /**
     * Suggest relevant knowledge base article titles for a ticket.
     *
     * @return string[]
     */
    public function suggestArticles(Ticket $ticket): array
    {
        $prompt = <<<PROMPT
Given this support ticket, suggest 3 knowledge base article titles that might help resolve it.
Return only a JSON array of strings, e.g. ["Title 1", "Title 2", "Title 3"].

Subject: {$ticket->subject}
Description: {$ticket->description}
PROMPT;

        $response = $this->chat($prompt);

        try {
            $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? array_slice($decoded, 0, 3) : [];
        } catch (\JsonException) {
            return [];
        }
    }

    /**
     * Generate a draft reply for an agent to send to the requester.
     */
    public function draftReply(Ticket $ticket): string
    {
        $prompt = <<<PROMPT
You are a helpful IT support agent. Write a professional, empathetic draft reply to the requester for the following ticket.
Keep it under 150 words. Do not include a subject line.

Subject: {$ticket->subject}
Description: {$ticket->description}
Current status: {$ticket->status}
PROMPT;

        return $this->chat($prompt);
    }

    /**
     * Send a chat completion request to the configured LLM endpoint.
     */
    private function chat(string $userMessage): string
    {
        if (empty($this->apiKey)) {
            return '[AI Assist is not configured. Set AI_ASSIST_API_KEY in .env]';
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->endpoint}/chat/completions", [
                    'model'    => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful IT service desk assistant.'],
                        ['role' => 'user',   'content' => $userMessage],
                    ],
                    'max_tokens'  => 512,
                    'temperature' => 0.3,
                ]);

            return $response->json('choices.0.message.content', '[No response from AI]');
        } catch (\Throwable $e) {
            Log::warning('AiAssistService: request failed', ['error' => $e->getMessage()]);

            return '[AI Assist unavailable: ' . $e->getMessage() . ']';
        }
    }
}
