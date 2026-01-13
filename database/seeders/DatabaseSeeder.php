<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            CountrySeeder::class,
            VatRateSeeder::class,
        ]);

        // Users
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

        // Demo data
        $this->call([
            EntitySeeder::class,
            ArticleSeeder::class,
        ]);
    }
}
