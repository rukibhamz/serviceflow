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
        'tenant_id',
        'ulid',
        'subject',
        'description',
        'status',
        'priority',
        'type',
        'change_type',
        'risk_level',
        'cab_approval_required',
        'scheduled_at',
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
            'custom_fields'        => 'array',
            'closed_at'            => 'datetime',
            'scheduled_at'         => 'datetime',
            'cab_approval_required'=> 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->ulid)) {
                $ticket->ulid = (string) \Illuminate\Support\Str::ulid();
            }
        });
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

    public function changeApprovers(): HasMany
    {
        return $this->hasMany(ChangeApprover::class);
    }

    public function isFullyApproved(): bool
    {
        return $this->changeApprovers()->exists()
            && $this->changeApprovers()->where('decision', '!=', 'approved')->orWhereNull('decision')->doesntExist();
    }

    public function hasRejection(): bool
    {
        return $this->changeApprovers()->where('decision', 'rejected')->exists();
    }
}
