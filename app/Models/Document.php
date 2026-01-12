<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    protected $fillable = [
        'type',
        'number',
        'entity_id',
        'date',
        'due_date',
        'status',
        'subtotal',
        'vat_total',
        'total',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /* =====================
     * RELAÇÕES
     * ===================== */
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function lines()
    {
        return $this->hasMany(DocumentLine::class);
    }

    /* =====================
     * LÓGICA DE NEGÓCIO
     * ===================== */

    public function recalculateTotals(): void
    {
        $subtotal = $this->lines()->sum('subtotal');
        $vat = $this->lines()->sum('vat_amount');

        $this->update([
            'subtotal' => $subtotal,
            'vat_total' => $vat,
            'total' => $subtotal + $vat,
        ]);
    }

    public static function nextNumber(string $type): int
    {
        return DB::transaction(function () use ($type) {
            $seq = Sequence::firstOrCreate(
                ['key' => $type],
                ['value' => 0]
            );

            $seq->increment('value');

            return $seq->value;
        });
    }
}
