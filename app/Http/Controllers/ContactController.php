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
            ->when(
                in_array($request->status, ['active', 'inactive'], true),
                fn ($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('contact_role_id') && is_numeric($request->contact_role_id),
                fn ($q) => $q->where('contact_role_id', (int) $request->contact_role_id)
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->search);

                $q->where(function ($qq) use ($search) {
                    $qq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            // ⬇️ IMPORTANTE: usar a relação contactRole (NUNCA "role")
            ->with('contactRole')
            ->orderBy('first_name')
            ->paginate($request->integer('per_page', 10))
            ->through(fn ($c) => [
                'id' => $c->id,
                'name' => trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')),
                'role' => $c->contactRole?->name, // ⬅️ aqui!
                'email' => $c->email,
                'phone' => $c->phone,
                'status' => $c->status,
                'contact_role_id' => $c->contact_role_id,
            ]);
    }

    public function store(Request $request, Entity $entity)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'contact_role_id' => ['nullable', 'exists:contact_roles,id'],
        ]);

        [$firstName, $lastName] = array_pad(explode(' ', $data['name'], 2), 2, null);

        $entity->contacts()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => 'active',
            'contact_role_id' => $data['contact_role_id'] ?? null,
        ]);

        return response()->json(['success' => true], 201);
    }

    public function update(Contact $contact, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'contact_role_id' => ['nullable', 'exists:contact_roles,id'],
        ]);

        [$firstName, $lastName] = array_pad(explode(' ', $data['name'], 2), 2, null);

        $contact->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'contact_role_id' => $data['contact_role_id'] ?? null,
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

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->noContent();
    }
}
