<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRole extends Model
{
    protected $fillable = ['name'];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'contact_role_id');
    }
}
