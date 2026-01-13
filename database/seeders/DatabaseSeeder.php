<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Garantir que roles existem (caso ainda não tenham sido seedadas)
        $this->call([
            PermissionSeeder::class,
            CountrySeeder::class,
            VatRateSeeder::class,
        ]);

        // --------------------
        // ADMIN
        // --------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@gestao.com'],
            [
                'name' => 'Admin',
                'phone' => null,
                'is_active' => true,
                'password' => bcrypt('password'),
            ]
        );
        $admin->syncRoles(['Admin']);

        // --------------------
        // FINANCEIRO
        // --------------------
        $finance = User::firstOrCreate(
            ['email' => 'financeiro@gestao.com'],
            [
                'name' => 'Financeiro',
                'phone' => null,
                'is_active' => true,
                'password' => bcrypt('password'),
            ]
        );
        $finance->syncRoles(['Financeiro']);

        // --------------------
        // COMERCIAL
        // --------------------
        $commercial = User::firstOrCreate(
            ['email' => 'comercial@gestao.com'],
            [
                'name' => 'Comercial',
                'phone' => null,
                'is_active' => true,
                'password' => bcrypt('password'),
            ]
        );
        $commercial->syncRoles(['Comercial']);
    }
}
