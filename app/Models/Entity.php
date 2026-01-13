<?php

namespace App\Models;

use App\Support\Hashing;
use App\Support\Normalize;
use Illuminate\Database\Eloquent\Model;
use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Builder;

class Entity extends Model
{
    protected $fillable = [
        'number',
        'nif_normalized',
        'name',
        'address',
        'country_id',
        'city',
        'postal_code',
        'notes',
        'email',
        'phone',
        'mobile',
        'website',
        'gdpr_consent',
        'is_customer',
        'is_supplier',
        'status',
    ];

    protected $casts = [
        // cifrados
        'address' => 'encrypted',
        'notes'   => 'encrypted',
        //'email'   => 'encrypted',
        'phone'   => 'encrypted',
        'mobile'  => 'encrypted',

        // boolean
        'gdpr_consent' => 'boolean',
        'is_customer'  => 'boolean',
        'is_supplier'  => 'boolean',
        'type' => EntityType::class,
    ];

    // Relações
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    // Normalizações + hashes (automaticamente ao gravar)
    public function setNifNormalizedAttribute($value): void
    {
        $this->attributes['nif_normalized'] = Normalize::vat($value) ?? '';
    }

    public function setEmailAttribute($value): void
    {
        $normalized = Normalize::email($value);

        // email NÃO é encriptado
        $this->attributes['email'] = $normalized;

        // hash continua OK
        $this->attributes['email_hash'] = Hashing::sha256($normalized);
    }


    public function setPhoneAttribute($value): void
    {
        $normalized = Normalize::digits($value);
        $this->attributes['phone'] = $normalized;
        $this->attributes['phone_hash'] = Hashing::sha256($normalized);
    }

    public function setMobileAttribute($value): void
    {
        $normalized = Normalize::digits($value);
        $this->attributes['mobile'] = $normalized;
        $this->attributes['mobile_hash'] = Hashing::sha256($normalized);
    }

    public function scopeClients(Builder $q): Builder
    {
        return $q->whereIn('type', [EntityType::CLIENT, EntityType::BOTH]);
    }

    public function scopeSuppliers(Builder $q): Builder
    {
        return $q->whereIn('type', [EntityType::SUPPLIER, EntityType::BOTH]);
    }

    public function scopeType(Builder $q, ?string $type): Builder
    {
        if (!$type) return $q;

        return match ($type) {
            'client' => $q->clients(),
            'supplier' => $q->suppliers(),
            'both' => $q->where('type', EntityType::BOTH),
            default => $q,
        };
    }

    protected static function booted(): void
    {
        static::saving(function ($entity) {
            if ($entity->type instanceof EntityType) {
                $entity->is_customer = in_array($entity->type, [EntityType::CLIENT, EntityType::BOTH], true);
                $entity->is_supplier = in_array($entity->type, [EntityType::SUPPLIER, EntityType::BOTH], true);
            }
        });
    }
}
