<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Página Inertia
     */
    public function page()
    {
        return Inertia::render('Access/Roles/Index');
    }

    /**
     * Lista de roles (JSON)
     */
    public function index()
    {
        return Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ]);
    }

    /**
     * Lista de permissões disponíveis
     */
    public function permissions()
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy(fn ($p) => explode('.', $p->name)[0]);
    }

    /**
     * Criar Role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Atualizar Role
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return response()->json(['success' => true]);
    }

    /**
     * Apagar Role
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'Admin') {
            return response()->json([
                'message' => 'O role Admin não pode ser removido.'
            ], 403);
        }

        $role->delete();

        return response()->json(['success' => true]);
    }
}
