<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entity;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class EntitySeeder extends Seeder
{
    public function run(): void
    {
        $number = 1;

        /*
        |--------------------------------------------------------------------------
        | FORNECEDORES (20)
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 20; $i++) {
            $email = "fornecedor{$i}@empresa.pt";
            $phone = '21' . rand(1000000, 9999999);

            Entity::create([
                'number'         => $number++,
                'nif_normalized' => 'PT' . rand(100000000, 999999999),
                'name'           => "Fornecedor {$i} Lda",
                'address'        => Crypt::encryptString("Rua do Fornecedor {$i}"),
                'city'           => 'Lisboa',
                'postal_code'    => '1000-00' . rand(1, 9),
                'email'          => $email,
                'email_hash'     => hash('sha256', strtolower($email)),
                'phone'          => $phone,
                'phone_hash'     => hash('sha256', $phone),
                'website'        => "https://fornecedor{$i}.pt",
                'gdpr_consent'   => true,
                'is_customer'    => false,
                'is_supplier'    => true,
                'status'         => 'active',
                'type'           => 'supplier',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENTES (45)
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 45; $i++) {
            $email = "cliente{$i}@empresa.pt";
            $phone = '91' . rand(1000000, 9999999);

            Entity::create([
                'number'         => $number++,
                'nif_normalized' => 'PT' . rand(100000000, 999999999),
                'name'           => "Cliente {$i} SA",
                'address'        => Crypt::encryptString("Avenida do Cliente {$i}"),
                'city'           => 'Porto',
                'postal_code'    => '4000-00' . rand(1, 9),
                'email'          => $email,
                'email_hash'     => hash('sha256', strtolower($email)),
                'phone'          => $phone,
                'phone_hash'     => hash('sha256', $phone),
                'website'        => "https://cliente{$i}.pt",
                'gdpr_consent'   => true,
                'is_customer'    => true,
                'is_supplier'    => false,
                'status'         => 'active',
                'type'           => 'client',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENTE + FORNECEDOR (5)
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 5; $i++) {
            $email = "parceiro{$i}@empresa.pt";
            $phone = '93' . rand(1000000, 9999999);

            Entity::create([
                'number'         => $number++,
                'nif_normalized' => 'PT' . rand(100000000, 999999999),
                'name'           => "Parceiro {$i} Group",
                'address'        => Crypt::encryptString("Rua do Parceiro {$i}"),
                'city'           => 'Coimbra',
                'postal_code'    => '3000-00' . rand(1, 9),
                'email'          => $email,
                'email_hash'     => hash('sha256', strtolower($email)),
                'phone'          => $phone,
                'phone_hash'     => hash('sha256', $phone),
                'website'        => "https://parceiro{$i}.pt",
                'gdpr_consent'   => true,
                'is_customer'    => true,
                'is_supplier'    => true,
                'status'         => 'active',
                'type'           => 'both',
            ]);
        }
    }
}
