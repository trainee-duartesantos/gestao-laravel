<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email',
            'status' => 'in:active,inactive',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
        ];
    }
}
