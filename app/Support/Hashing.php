<?php

namespace App\Support;

class Hashing
{
    public static function sha256(?string $value): ?string
    {
        if ($value === null) return null;

        $v = trim($value);
        if ($v === '') return null;

        return hash('sha256', $v);
    }
}
