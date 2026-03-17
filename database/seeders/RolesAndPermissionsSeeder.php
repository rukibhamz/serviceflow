<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'tickets.comment',
            'tickets.assign',
            'reports.view',
            'reports.export',
            'settings.view',
            'settings.update',
            'portal.tickets.create',
            'portal.tickets.view',
            'portal.tickets.comment',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $agent = Role::firstOrCreate(['name' => 'agent']);
        $endUser = Role::firstOrCreate(['name' => 'end_user']);

        $admin->givePermissionTo(Permission::all());

        $manager->givePermissionTo([
            'tickets.view',
            'tickets.update',
            'tickets.comment',
            'tickets.assign',
            'reports.view',
            'reports.export',
            'settings.view',
        ]);

        $agent->givePermissionTo([
            'tickets.view',
            'tickets.update',
            'tickets.comment',
        ]);

        $endUser->givePermissionTo([
            'portal.tickets.create',
            'portal.tickets.view',
            'portal.tickets.comment',
        ]);
    }
}
