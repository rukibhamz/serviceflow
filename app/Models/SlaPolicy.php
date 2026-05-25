<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'priority',
        'ticket_type',
        'response_minutes',
        'resolution_minutes',
        'business_hours',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'business_hours' => 'array',
            'is_default'     => 'boolean',
            'is_active'      => 'boolean',
        ];
    }

    public function timers(): HasMany
    {
        return $this->hasMany(SlaTimer::class);
    }
}
