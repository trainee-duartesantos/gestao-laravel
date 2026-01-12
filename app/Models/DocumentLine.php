<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentLine extends Model
{
    protected $fillable = [
        'document_id',
        'article_id',
        'description',
        'quantity',
        'unit_price',
        'vat_rate',
        'subtotal',
        'vat_amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public static function makeFromArticle(
        Document $document,
        Article $article,
        float $quantity = 1
    ): self {
        $subtotal = $quantity * $article->price;
        $vatAmount = $subtotal * ($article->vatRate->rate / 100);

        return self::create([
            'document_id' => $document->id,
            'article_id' => $article->id,
            'description' => $article->name,
            'quantity' => $quantity,
            'unit_price' => $article->price,
            'vat_rate' => $article->vatRate->rate,
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total' => $subtotal + $vatAmount,
        ]);
    }
}
