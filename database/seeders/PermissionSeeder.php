<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'clients',
            'suppliers',
            'contacts',
            'articles',
            'proposals',
            'orders',
            'invoices',
            'settings',
            'users',
            'logs',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}"
                ]);
            }
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());

        $finance = Role::firstOrCreate(['name' => 'Financeiro']);
        $finance->syncPermissions([
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'settings.view',
        ]);

        $commercial = Role::firstOrCreate(['name' => 'Comercial']);
        $commercial->syncPermissions([
            'clients.view',
            'clients.create',
            'clients.edit',
            'proposals.view',
            'proposals.create',
            'proposals.edit',
        ]);

        $operator = Role::firstOrCreate(['name' => 'Operador']);
        $operator->syncPermissions([
            'clients.view',
            'contacts.view',
        ]);
    }
}
