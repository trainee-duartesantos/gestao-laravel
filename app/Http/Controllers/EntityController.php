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
        $type = $request->string('type')->toString();
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

        if (in_array($type, ['client', 'supplier', 'both'], true)) {
            match ($type) {
                'client' => $query->whereIn('type', ['client', 'both']),
                'supplier' => $query->whereIn('type', ['supplier', 'both']),
                'both' => $query->where('type', 'both'),
            };
        }

        return $query
            ->paginate($perPage)
            ->through(fn ($e) => [
                'id' => $e->id,
                'number' => $e->number,
                'name' => $e->name,
                'nif_normalized' => $e->nif_normalized,
                'status' => $e->status,
                'type' => $e->type,
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
            'type' => ['required', Rule::in(['client', 'supplier', 'both'])],
        ]);

        $entity = DB::transaction(function () use ($validated) {
            $nextNumber = (Entity::max('number') ?? 0) + 1;

            return Entity::create([
                'number' => $nextNumber,
                'name' => $validated['name'],
                'nif_normalized' => $validated['nif'],
                'email' => $validated['email'] ?? null,
                'type' => $validated['type'],
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
            'type' => ['required', Rule::in(['client', 'supplier', 'both'])],
        ]);

        $entity->update([
            'name' => $validated['name'],
            'nif_normalized' => $validated['nif'],
            'email' => $validated['email'] ?? null,
            'type' => $validated['type'],
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
    
    public function destroy(Entity $entity)
    {
        if ($entity->contacts()->exists()) {
            return response()->json([
                'message' => 'Não é possível eliminar entidades com contactos associados.'
            ], 422);
        }

        $entity->delete();
        return response()->noContent();
    }
}