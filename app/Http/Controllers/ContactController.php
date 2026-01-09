<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function page(Entity $entity)
    {
        return Inertia::render('Contacts/Index', [
            'entity' => $entity,
        ]);
    }

    public function index(Entity $entity, Request $request)
    {
        return $entity->contacts()
            ->when($request->status === 'active', fn ($q) => $q->where('status', 'active'))
            ->when($request->status === 'inactive', fn ($q) => $q->where('status', 'inactive'))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($qq) use ($request) {
                    $qq->where('first_name', 'like', "%{$request->search}%")
                       ->orWhere('last_name', 'like', "%{$request->search}%")
                       ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('first_name')
            ->paginate($request->integer('per_page', 10))
            ->through(fn ($c) => [
                'id'     => $c->id,
                'name'   => trim($c->first_name . ' ' . $c->last_name),
                'role'   => $c->role,
                'email'  => $c->email,
                'phone'  => $c->phone,
                'status' => $c->status,
            ]);
    }

    public function store(Request $request, Entity $entity)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'role'  => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
        ]);

        [$firstName, $lastName] = array_pad(explode(' ', $data['name'], 2), 2, null);

        $entity->contacts()->create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'role'       => $data['role'] ?? null,
            'email'      => $data['email'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'status'     => 'active', // ✅ ATIVO POR DEFEITO
        ]);

        return response()->json(['success' => true], 201);
    }

    public function update(Contact $contact, Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'role'  => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
        ]);

        [$firstName, $lastName] = array_pad(explode(' ', $data['name'], 2), 2, null);

        $contact->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'role'       => $data['role'] ?? null,
            'email'      => $data['email'] ?? null,
            'phone'      => $data['phone'] ?? null,
        ]);

        return response()->noContent();
    }

    public function toggleStatus(Contact $contact)
    {
        $contact->update([
            'status' => $contact->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json(['success' => true]);
    }
}
