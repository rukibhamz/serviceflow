<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use App\Services\Sla\SlaService;

class RecordFirstResponse
{
    public function __construct(private SlaService $slaService) {}

    public function handle(CommentAdded $event): void
    {
        $comment = $event->comment;
        $this->slaService->recordFirstResponse($comment->ticket, $comment);
    }
}
