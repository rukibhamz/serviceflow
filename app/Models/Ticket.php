<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ticket extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'ulid',
        'subject',
        'description',
        'status',
        'priority',
        'type',
        'source',
        'requester_id',
        'assignee_id',
        'team_id',
        'merged_into_id',
        'custom_fields',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'closed_at'     => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function slaTimers(): HasMany
    {
        return $this->hasMany(SlaTimer::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function emailThreads(): HasMany
    {
        return $this->hasMany(EmailThread::class);
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_watchers');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TicketTag::class, 'ticket_tag');
    }

    public function relations(): HasMany
    {
        return $this->hasMany(TicketRelation::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Ticket::class, 'merged_into_id');
    }

    public function csatSurveys(): HasMany
    {
        return $this->hasMany(CsatSurvey::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_ticket');
    }
}
