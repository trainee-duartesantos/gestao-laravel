<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function page()
    {
        return Inertia::render('Access/Users/Index');
    }

    public function index()
    {
        return User::with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'role' => $user->roles->first()?->name,
            ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'is_active' => $data['is_active'],
            'password' => bcrypt(str()->random(32)),
        ]);

        $user->syncRoles([$data['role']]);

        // Enviar email para definir password
        Password::sendResetLink(['email' => $user->email]);

        return back()->with('success', 'Utilizador criado com sucesso.');
    }

    public function update(Request $request, User $user)
    {
        // Segurança: não pode desativar-se a si próprio
        if ($request->user()->id === $user->id && ! $request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'Não pode desativar o seu próprio utilizador.']);
        }

        // Proteger Admin
        if ($user->hasRole('Admin') && ! $request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'Não pode desativar um utilizador Admin.']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'role' => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
        ]);

        $user->update($data);
        $user->syncRoles([$data['role']]);

        return back()->with('success', 'Utilizador atualizado com sucesso.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['delete' => 'Não pode apagar o seu próprio utilizador.']);
        }

        if ($user->hasRole('Admin')) {
            return back()->withErrors(['delete' => 'Não pode apagar um utilizador Admin.']);
        }

        $user->delete();

        return back()->with('success', 'Utilizador removido com sucesso.');
    }
}
