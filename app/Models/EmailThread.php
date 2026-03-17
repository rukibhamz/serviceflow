<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'message_id',
        'in_reply_to',
        'from_address',
        'from_name',
        'direction',
        'raw_headers',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
