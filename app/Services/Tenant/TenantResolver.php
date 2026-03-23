<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * Resolves the current tenant from the HTTP request.
 *
 * Resolution order:
 *   1. Subdomain: {subdomain}.example.com
 *   2. Path prefix: example.com/{subdomain}/...
 *
 * Once resolved, the tenant is bound into the service container
 * and its settings are merged into the application config.
 */
class TenantResolver
{
    private ?Tenant $current = null;

    /**
     * Resolve and set the current tenant from the request.
     * Returns null if no tenant could be resolved.
     */
    public function resolve(Request $request): ?Tenant
    {
        $tenant = $this->fromSubdomain($request)
            ?? $this->fromPathPrefix($request);

        if ($tenant && $tenant->is_active) {
            $this->setCurrent($tenant);
        }

        return $this->current;
    }

    /**
     * Resolve tenant from subdomain (e.g. acme.serviceflow.app).
     */
    private function fromSubdomain(Request $request): ?Tenant
    {
        $host  = $request->getHost();
        $parts = explode('.', $host);

        if (count($parts) < 3) {
            return null; // no subdomain
        }

        $subdomain = $parts[0];

        return Tenant::where('subdomain', $subdomain)->where('is_active', true)->first();
    }

    /**
     * Resolve tenant from path prefix (e.g. /acme/tickets).
     */
    private function fromPathPrefix(Request $request): ?Tenant
    {
        $segments = $request->segments();

        if (empty($segments)) {
            return null;
        }

        return Tenant::where('subdomain', $segments[0])->where('is_active', true)->first();
    }

    /**
     * Set the current tenant and merge its settings into config.
     */
    public function setCurrent(Tenant $tenant): void
    {
        $this->current = $tenant;

        // Merge tenant-specific settings into config
        foreach ($tenant->settings ?? [] as $key => $value) {
            config(["tenant.{$key}" => $value]);
        }

        config(['tenant.id'        => $tenant->id]);
        config(['tenant.subdomain' => $tenant->subdomain]);
        config(['tenant.name'      => $tenant->name]);
    }

    public function current(): ?Tenant
    {
        return $this->current;
    }

    public function currentId(): ?int
    {
        return $this->current?->id;
    }
}
