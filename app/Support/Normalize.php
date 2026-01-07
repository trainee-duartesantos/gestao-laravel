<?php

namespace App\Support;

class Normalize
{
    public static function email(?string $email): ?string
    {
        if ($email === null) return null;
        $e = strtolower(trim($email));
        return $e === '' ? null : $e;
    }

    public static function digits(?string $value): ?string
    {
        if ($value === null) return null;
        $d = preg_replace('/\D+/', '', $value);
        return $d === '' ? null : $d;
    }

    public static function vat(?string $vat): ?string
    {
        if ($vat === null) return null;
        $v = strtoupper(trim($vat));
        $v = preg_replace('/\s+/', '', $v);
        return $v === '' ? null : $v;
    }
}
