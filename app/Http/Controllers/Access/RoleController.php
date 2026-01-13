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
    public function permissions(Role $role)
    {
        return response()->json([
            'role' => $role->only('id', 'name'),
            'permissions' => Permission::all()
                ->groupBy(fn ($p) => explode('.', $p->name)[0])
                ->map(fn ($group) => $group->pluck('name')->values()),
            'assigned' => $role->permissions->pluck('name')->values(),
        ]);
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
        $data = $request->validate([
            'name' => ['sometimes', 'string'],
            'permissions' => ['sometimes', 'array'],
        ]);

        if (isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return response()->noContent();
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
