<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // mais tarde ligamos a permissões
    }

    public function rules(): array
    {
        return [
            'nif' => 'required|string|max:32',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
        ];
    }
}
