<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Services\NumberService;
use App\Support\Hashing;
use App\Support\Normalize;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    // LISTAGEM + FILTROS + PESQUISA
    public function index(Request $request)
    {
        $query = Entity::query();

        // filtros
        if ($request->boolean('is_customer')) {
            $query->where('is_customer', true);
        }

        if ($request->boolean('is_supplier')) {
            $query->where('is_supplier', true);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // pesquisa por NIF
        if ($request->nif) {
            $query->where('nif_normalized', Normalize::vat($request->nif));
        }

        // pesquisa por email (hash)
        if ($request->email) {
            $hash = Hashing::sha256(Normalize::email($request->email));
            $query->where('email_hash', $hash);
        }

        return response()->json(
            $query->orderBy('number', 'desc')->paginate(15)
        );
    }

    // CRIAR
    public function store(StoreEntityRequest $request)
    {
        $number = NumberService::next('entity');

        $entity = Entity::create([
            'number' => $number,
            'nif_normalized' => $request->nif,
            'name' => $request->name,
            'email' => $request->email,
            'is_customer' => $request->boolean('is_customer', true),
            'is_supplier' => $request->boolean('is_supplier', false),
        ]);

        return response()->json($entity, 201);
    }

    // DETALHE
    public function show(Entity $entity)
    {
        return response()->json($entity->load('contacts'));
    }

    // ATUALIZAR
    public function update(UpdateEntityRequest $request, Entity $entity)
    {
        $entity->update($request->validated());
        return response()->json($entity);
    }

    // DESATIVAR (soft business delete)
    public function destroy(Entity $entity)
    {
        $entity->update(['status' => 'inactive']);
        return response()->json(['message' => 'Entity deactivated']);
    }
}
