<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'price',
        'vat_rate_id',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function vatRate()
    {
        return $this->belongsTo(VatRate::class);
    }

    // Scopes úteis
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeType($q, $type)
    {
        if (!$type) return $q;

        return $q->where('type', $type);
    }
}
