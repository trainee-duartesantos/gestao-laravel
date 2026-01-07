<?php

namespace App\Models;

use App\Support\Hashing;
use App\Support\Normalize;
use Illuminate\Database\Eloquent\Model;

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
        'email'   => 'encrypted',
        'phone'   => 'encrypted',
        'mobile'  => 'encrypted',

        // boolean
        'gdpr_consent' => 'boolean',
        'is_customer'  => 'boolean',
        'is_supplier'  => 'boolean',
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
        $this->attributes['email'] = $normalized; // cast encriptado faz o resto
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
}
