<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Asset extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'asset_tag',
        'type',
        'serial_number',
        'assigned_to',
        'status',
        'purchased_at',
        'eol_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta'         => 'array',
            'purchased_at' => 'date',
            'eol_at'       => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'asset_ticket');
    }
}
