<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaTimer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'sla_policy_id',
        'type',
        'due_at',
        'paused_at',
        'paused_minutes',
        'breached',
        'stopped_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at'     => 'datetime',
            'paused_at'  => 'datetime',
            'stopped_at' => 'datetime',
            'breached'   => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function pauses(): HasMany
    {
        return $this->hasMany(SlaPause::class);
    }
}
