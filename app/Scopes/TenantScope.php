<?php

namespace App\Scopes;

use App\Services\Tenant\TenantResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Eloquent scope that automatically filters queries by the current tenant_id.
 *
 * Apply to any model with:
 *   protected static function booted(): void
 *   {
 *       static::addGlobalScope(new TenantScope());
 *   }
 *
 * To bypass (e.g. in admin/provisioning code):
 *   Model::withoutGlobalScope(TenantScope::class)->get();
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = app(TenantResolver::class)->currentId();

        if ($tenantId !== null) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }

    /**
     * Extend the builder with a macro to set tenant_id on create.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
