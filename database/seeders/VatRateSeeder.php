<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VatRate;

class VatRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['name' => 'Isento', 'rate' => 0.00],
            ['name' => 'Reduzida', 'rate' => 6.00],
            ['name' => 'Intermédia', 'rate' => 13.00],
            ['name' => 'Normal', 'rate' => 23.00],
        ];

        foreach ($rates as $rate) {
            VatRate::firstOrCreate(
                ['rate' => $rate['rate']],
                ['name' => $rate['name'], 'is_active' => true]
            );
        }
    }
}
