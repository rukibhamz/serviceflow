<?php

namespace App\Models;

use App\Events\CommentAdded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TicketComment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (self $comment) {
            event(new CommentAdded($comment));
        });
    }

    protected $fillable = [
        'ticket_id',
        'user_id',
        'body',
        'is_internal',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'is_system'   => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
