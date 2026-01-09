<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CountryController extends Controller
{
    // Página Inertia
    public function page()
    {
        return Inertia::render('Settings/Countries/Index');
    }

    // Listagem (JSON)
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();
        $perPage = $request->integer('per_page', 10);

        $query = Country::query()->orderBy('name');

        if ($search->isNotEmpty()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query
            ->paginate($perPage)
            ->through(fn ($c) => [
                'id'   => $c->id,
                'code' => $c->code,
                'name' => $c->name,
            ]);
    }

    // Criar
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:2', 'unique:countries,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Country::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
        ]);

        return response()->json(['success' => true], 201);
    }

    // Atualizar
    public function update(Request $request, Country $country)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:2', 'unique:countries,code,' . $country->id],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $country->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
        ]);

        return response()->json(['success' => true]);
    }

    // Eliminar
    public function destroy(Country $country)
    {
        $country->delete();

        return response()->noContent();
    }
}
