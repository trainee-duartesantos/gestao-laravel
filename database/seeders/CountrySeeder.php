<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['code' => 'PT', 'name' => 'Portugal'],
            ['code' => 'ES', 'name' => 'Espanha'],
            ['code' => 'FR', 'name' => 'França'],
            ['code' => 'DE', 'name' => 'Alemanha'],
            ['code' => 'IT', 'name' => 'Itália'],
            ['code' => 'NL', 'name' => 'Países Baixos'],
            ['code' => 'BE', 'name' => 'Bélgica'],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['code' => $country['code']],
                ['name' => $country['name']]
            );
        }
    }
}
