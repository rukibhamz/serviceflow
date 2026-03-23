<?php

namespace App\Services\Tenant;

use App\Models\SlaPolicy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Provisions a new tenant: creates the tenant record, seeds default data,
 * and returns the provisioned tenant with its admin user.
 */
class TenantProvisioner
{
    /**
     * Provision a new tenant.
     *
     * @param  array{name: string, subdomain: string, admin_name: string, admin_email: string, admin_password: string}  $data
     * @return array{tenant: Tenant, admin: User}
     *
     * @throws \InvalidArgumentException if subdomain is already taken
     */
    public function provision(array $data): array
    {
        $subdomain = Str::slug($data['subdomain']);

        if (Tenant::where('subdomain', $subdomain)->exists()) {
            throw new \InvalidArgumentException("Subdomain '{$subdomain}' is already taken.");
        }

        // Create tenant record
        $tenant = Tenant::create([
            'name'      => $data['name'],
            'subdomain' => $subdomain,
            'is_active' => true,
            'settings'  => $data['settings'] ?? [],
        ]);

        // Create admin user scoped to this tenant
        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name'      => $data['admin_name'],
            'email'     => $data['admin_email'],
            'password'  => Hash::make($data['admin_password']),
        ]);

        $admin->assignRole('admin');

        // Seed default SLA policies for the tenant
        $this->seedDefaultSla($tenant->id);

        return compact('tenant', 'admin');
    }

    /**
     * Suspend a tenant (sets is_active = false).
     */
    public function suspend(Tenant $tenant): void
    {
        $tenant->is_active = false;
        $tenant->save();
    }

    /**
     * Activate a previously suspended tenant.
     */
    public function activate(Tenant $tenant): void
    {
        $tenant->is_active = true;
        $tenant->save();
    }

    private function seedDefaultSla(int $tenantId): void
    {
        $defaults = [
            ['priority' => 'critical', 'response_minutes' => 15,  'resolution_minutes' => 240],
            ['priority' => 'high',     'response_minutes' => 60,  'resolution_minutes' => 480],
            ['priority' => 'medium',   'response_minutes' => 120, 'resolution_minutes' => 720],
            ['priority' => 'low',      'response_minutes' => 240, 'resolution_minutes' => 1440],
        ];

        foreach ($defaults as $sla) {
            SlaPolicy::create(array_merge($sla, [
                'tenant_id'  => $tenantId,
                'name'       => ucfirst($sla['priority']) . ' SLA',
                'is_active'  => true,
                'is_default' => $sla['priority'] === 'medium',
            ]));
        }
    }
}
