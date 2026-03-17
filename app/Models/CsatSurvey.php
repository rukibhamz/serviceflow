<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsatSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'requester_id',
        'token',
        'rating',
        'comment',
        'sent_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at'      => 'datetime',
            'responded_at' => 'datetime',
            'rating'       => 'integer',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
