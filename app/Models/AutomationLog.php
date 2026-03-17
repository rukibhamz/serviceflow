<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id',
        'ticket_id',
        'conditions_snapshot',
        'actions_executed',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'conditions_snapshot' => 'array',
            'actions_executed'    => 'array',
        ];
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
