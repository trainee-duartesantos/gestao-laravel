<?php

namespace App\Http\Controllers;
use App\Models\Entity;
use App\Models\Contact;
use Inertia\Inertia;


use Illuminate\Http\Request;

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
            ->orderBy('first_name')
            ->paginate($request->integer('per_page', 10))
            ->through(fn ($c) => [
                'id' => $c->id,
                'name' => trim($c->first_name . ' ' . $c->last_name),
                'role' => $c->role,
                'email' => $c->email,
                'phone' => $c->phone,
                'status' => $c->status,
            ]);
    }

    public function store(Entity $entity, Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['nullable', 'string'],
            'role' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'gdpr_consent' => ['boolean'],
        ]);

        $entity->contacts()->create($validated);

        return response()->noContent();
    }

    public function update(Contact $contact, Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['nullable', 'string'],
            'role' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'gdpr_consent' => ['boolean'],
        ]);

        $contact->update($validated);

        return response()->noContent();
    }

    public function toggleStatus(Contact $contact)
    {
        $contact->update([
            'status' => $contact->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->noContent();
    }

}

