<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ServiceCatalogueItem extends Model
{
    protected $fillable = [
        'tenant_id',
        'team_id',
        'created_by',
        'slug',
        'name',
        'description',
        'type',
        'priority',
        'fields',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * True when the catalogue migration has been applied (table exists).
     * Deployments may pull code before `php artisan migrate` runs on older databases.
     */
    public static function isAvailable(): bool
    {
        return Schema::hasTable((new static)->getTable());
    }
}

