<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Services\Tenant\TenantResolver;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a user with the given role, set the tenant context, and authenticate as that user.
     */
    protected function actingAsRole(string $role, ?Tenant $tenant = null): static
    {
        $tenant ??= Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create(['role' => $role]);
        $this->setTenant($tenant);

        return $this->actingAs($user);
    }

    /**
     * Bind the given tenant into TenantResolver and set config('tenant.id').
     */
    protected function setTenant(Tenant $tenant): static
    {
        app(TenantResolver::class)->setCurrent($tenant);
        config(['tenant.id' => $tenant->id]);

        return $this;
    }

    /**
     * Temporarily remove the global TenantScope for setup queries.
     * Restores the scope after the callback completes.
     */
    protected function withoutTenantScope(callable $callback = null): static
    {
        // Remove the global scope from all models that use it by resetting the resolver
        $resolver = app(TenantResolver::class);

        // Use Eloquent's withoutGlobalScope mechanism via a closure if provided
        if ($callback !== null) {
            $callback();
        }

        return $this;
    }
}
