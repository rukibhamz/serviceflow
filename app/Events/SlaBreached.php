<?php

namespace App\Events;

use App\Models\SlaTimer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlaBreached
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly SlaTimer $timer) {}
}
