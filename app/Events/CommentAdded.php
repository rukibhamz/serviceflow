<?php

namespace App\Events;

use App\Models\TicketComment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly TicketComment $comment) {}
}
