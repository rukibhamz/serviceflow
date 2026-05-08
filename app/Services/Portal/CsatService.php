<?php

namespace App\Services\Portal;

use App\Mail\CsatSurveyMail;
use App\Models\CsatSurvey;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CsatService
{
    /**
     * Send a CSAT survey email when a ticket is closed.
     * Creates one survey record per ticket per requester (idempotent).
     */
    public function sendSurvey(Ticket $ticket): ?CsatSurvey
    {
        $requester = $ticket->requester;

        if ($requester === null) {
            return null;
        }

        // Enforce one survey per ticket per requester
        $survey = CsatSurvey::firstOrCreate(
            ['ticket_id' => $ticket->id, 'requester_id' => $requester->id],
            ['token' => Str::random(40), 'sent_at' => now()],
        );

        // Queue exactly once for idempotent sendSurvey calls.
        if ($survey->wasRecentlyCreated) {
            Mail::to($requester->email)->queue(new CsatSurveyMail($ticket, $survey));
        }

        return $survey;
    }

    /**
     * Record a CSAT rating via the tokenised URL.
     * Enforces one response per survey — subsequent calls update the rating.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function recordRating(string $token, int $rating, ?string $comment = null): CsatSurvey
    {
        $survey = CsatSurvey::where('token', $token)->firstOrFail();

        $survey->update([
            'rating'       => max(1, min(5, $rating)), // clamp to 1–5
            'comment'      => $comment,
            'responded_at' => $survey->responded_at ?? now(), // only stamp first response
        ]);

        return $survey->fresh();
    }
}
