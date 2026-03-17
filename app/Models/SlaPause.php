<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPause extends Model
{
    use HasFactory;

    protected $fillable = [
        'sla_timer_id',
        'paused_at',
        'resumed_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'paused_at'  => 'datetime',
            'resumed_at' => 'datetime',
        ];
    }

    public function timer(): BelongsTo
    {
        return $this->belongsTo(SlaTimer::class);
    }
}
