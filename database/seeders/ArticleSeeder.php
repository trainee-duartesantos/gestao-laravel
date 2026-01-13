<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\VatRate;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Consultoria Técnica',
            'Desenvolvimento Web',
            'Manutenção Mensal',
            'Licenciamento Software',
            'Formação Técnica',
            'Auditoria de Segurança',
            'Suporte Premium',
            'Análise de Requisitos',
            'Design UI/UX',
            'Integração API',
        ];

        $vatRates = VatRate::pluck('id')->toArray();

        if (empty($vatRates)) {
            $this->command->warn('⚠️ Nenhuma taxa de IVA encontrada. Seeder de artigos ignorado.');
            return;
        }

        for ($i = 1; $i <= 50; $i++) {
            Article::create([
                'code'        => 'ART-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name'        => $names[array_rand($names)] . " {$i}",
                'type'        => rand(0, 1) ? 'service' : 'product',
                'price'       => rand(50, 1500),
                'vat_rate_id' => $vatRates[array_rand($vatRates)], // 🔥 IVA variável
                'status'      => 'active',
            ]);
        }
    }
}
