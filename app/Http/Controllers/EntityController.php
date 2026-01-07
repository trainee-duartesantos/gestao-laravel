<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Services\NumberService;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nif'  => 'required|string',
            'name' => 'required|string',
        ]);

        // gerar número UMA vez
        $number = NumberService::next('entity');

        $entity = Entity::create([
            'number' => $number,
            'nif_normalized' => $request->nif,
            'name' => $request->name,
            'email' => $request->email ?? null,
            'is_customer' => true,
            'is_supplier' => false,
        ]);

        return response()->json([
            'id' => $entity->id,
            'number' => $entity->number,
            'formatted_number' => 'ENT-' . str_pad($number, 5, '0', STR_PAD_LEFT),
        ], 201);
    }
}
