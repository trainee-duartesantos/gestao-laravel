<?php

namespace App\Models;

use App\Support\Hashing;
use App\Support\Normalize;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'entity_id',
        'first_name',
        'last_name',
        'email',
        'email_hash',
        'phone',
        'phone_hash',
        'status',
        'contact_role_id',
    ];

    protected $casts = [
        'gdpr_consent' => 'boolean',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    // ✅ relação oficial (evita colisões com coluna "role")
    public function contactRole()
    {
        return $this->belongsTo(ContactRole::class, 'contact_role_id');
    }

    public function setEmailAttribute($value): void
    {
        $normalized = Normalize::email($value);
        $this->attributes['email'] = $normalized;
        $this->attributes['email_hash'] = Hashing::sha256($normalized);
    }

    public function setPhoneAttribute($value): void
    {
        $normalized = Normalize::digits($value);
        $this->attributes['phone'] = $normalized;
        $this->attributes['phone_hash'] = Hashing::sha256($normalized);
    }
}
