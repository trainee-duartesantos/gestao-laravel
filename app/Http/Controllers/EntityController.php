<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class EntityController extends Controller
{
    // Página Inertia
    public function page()
    {
        return Inertia::render('Entities/Index');
    }

    // Listagem (JSON)
    public function index()
    {
        return Entity::orderBy('number')
            ->paginate(15)
            ->through(fn ($e) => [
                'id' => $e->id,
                'number' => $e->number,
                'name' => $e->name,
                'nif_normalized' => $e->nif_normalized,
                'status' => $e->status,
            ]);
    }

    // Criar entidade
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nif' => [
                'required',
                'string',
                Rule::unique('entities', 'nif_normalized'),
            ],
            'email' => ['nullable', 'email'],
            'is_customer' => ['boolean'],
            'is_supplier' => ['boolean'],
        ]);

        $entity = DB::transaction(function () use ($validated) {
            $nextNumber = (Entity::max('number') ?? 0) + 1;

            return Entity::create([
                'number' => $nextNumber,
                'name' => $validated['name'],
                'nif_normalized' => $validated['nif'],
                'email' => $validated['email'] ?? null,
                'is_customer' => $validated['is_customer'] ?? false,
                'is_supplier' => $validated['is_supplier'] ?? false,
                'status' => 'active',
            ]);
        });

        return response()->json([
            'success' => true,
            'id' => $entity->id,
        ], 201);
    }
}