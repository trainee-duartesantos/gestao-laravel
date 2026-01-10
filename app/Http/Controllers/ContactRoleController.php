<?php

namespace App\Http\Controllers;

use App\Models\ContactRole;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Entity;

class ContactRoleController extends Controller
{
    // Página Inertia
    public function page()
    {
        return Inertia::render('Settings/ContactRoles/Index');
    }

    // Listagem
    public function index(Request $request)
    {
        $search  = $request->string('search')->trim();
        $perPage = $request->integer('per_page', 10);

        $query = ContactRole::query()
            ->withCount('contacts')
            ->orderBy('name');

        if ($search->isNotEmpty()) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    // Criar
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:contact_roles,name'],
        ]);

        ContactRole::create($data);

        return response()->json(['success' => true], 201);
    }

    // Atualizar
    public function update(Request $request, ContactRole $contactRole)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:contact_roles,name,' . $contactRole->id,
            ],
        ]);

        $contactRole->update($data);

        return response()->json(['success' => true]);
    }

    // Eliminar
    public function destroy(ContactRole $contactRole)
    {
        if ($contactRole->contacts()->exists()) {
            return response()->json([
                'message' => 'Não é possível eliminar esta função porque está associada a contactos.'
            ], 422);
        }

        $contactRole->delete();

        return response()->noContent();
    }
}
