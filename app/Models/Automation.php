<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Automation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'trigger_event',
        'conditions',
        'actions',
        'is_active',
        'run_count',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'conditions'  => 'array',
            'actions'     => 'array',
            'is_active'   => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class);
    }
}
