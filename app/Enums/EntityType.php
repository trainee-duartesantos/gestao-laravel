<?php

namespace App\Enums;

enum EntityType: string
{
    case CLIENT = 'client';
    case SUPPLIER = 'supplier';
    case BOTH = 'both';

    public function label(): string
    {
        return match ($this) {
            self::CLIENT => 'Cliente',
            self::SUPPLIER => 'Fornecedor',
            self::BOTH => 'Ambos',
        };
    }
}
