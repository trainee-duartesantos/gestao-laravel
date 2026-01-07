<?php

namespace App\Services;

use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class NumberService
{
    /**
     * Retorna o próximo número de uma sequência de forma segura
     */
    public static function next(string $key): int
    {
        return DB::transaction(function () use ($key) {

            $sequence = Sequence::lockForUpdate()->firstOrCreate(
                ['key' => $key],
                ['value' => 0]
            );

            $sequence->value++;
            $sequence->save();

            return $sequence->value;
        });
    }
}
