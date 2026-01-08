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
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();
        $status = $request->string('status');
        $perPage = $request->integer('per_page', 10);

        $query = Entity::query()
            ->orderBy('number');

        // 🔍 Pesquisa por nome ou NIF
        if ($search->isNotEmpty()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('nif_normalized', 'like', "%{$search}%");
            });
        }

        // 🔘 Filtro por estado
        if (in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status);
        }

        return $query
            ->paginate($perPage)
            ->through(fn ($e) => [
                'id' => $e->id,
                'number' => $e->number,
                'name' => $e->name,
                'nif_normalized' => $e->nif_normalized,
                'status' => $e->status,
                'is_customer' => $e->is_customer,
                'is_supplier' => $e->is_supplier,
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

    public function update(Request $request, Entity $entity)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nif' => [
                'required',
                'string',
                Rule::unique('entities', 'nif_normalized')->ignore($entity->id),
            ],
            'email' => ['nullable', 'email'],
            'is_customer' => ['boolean'],
            'is_supplier' => ['boolean'],
        ]);

        $entity->update([
            'name' => $validated['name'],
            'nif_normalized' => $validated['nif'],
            'email' => $validated['email'] ?? null,
            'is_customer' => $validated['is_customer'] ?? false,
            'is_supplier' => $validated['is_supplier'] ?? false,
        ]);

        return response()->json(['success' => true]);
    }

    public function toggleStatus(Entity $entity)
    {
        $entity->update([
            'status' => $entity->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'status' => $entity->status,
        ]);
    }

}