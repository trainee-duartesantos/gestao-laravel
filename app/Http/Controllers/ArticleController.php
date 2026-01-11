<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\VatRate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    // Página Inertia
    public function page()
    {
        return Inertia::render('Articles/Index', [
            'vatRates' => VatRate::orderBy('rate')->get(['id', 'name', 'rate']),
        ]);
    }

    // Listagem JSON
    public function index(Request $request)
    {
        return Article::query()
            ->when($request->filled('search'), fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
            )
            ->when($request->filled('type'), fn ($q) =>
                $q->where('type', $request->type)
            )
            ->when(in_array($request->status, ['active', 'inactive']), fn ($q) =>
                $q->where('status', $request->status)
            )
            ->with('vatRate')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 10))
            ->through(fn ($a) => [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'type' => $a->type,
                'price' => $a->price,
                'vat' => $a->vatRate?->rate,
                'status' => $a->status,
            ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:articles,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['product', 'service'])],
            'price' => ['required', 'numeric', 'min:0'],
            'vat_rate_id' => ['required', 'exists:vat_rates,id'],
        ]);

        Article::create($data);

        return response()->json(['success' => true], 201);
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('articles')->ignore($article->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['product', 'service'])],
            'price' => ['required', 'numeric', 'min:0'],
            'vat_rate_id' => ['required', 'exists:vat_rates,id'],
        ]);

        $article->update($data);

        return response()->json(['success' => true]);
    }

    public function toggleStatus(Article $article)
    {
        $article->update([
            'status' => $article->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json(['status' => $article->status]);
    }
}
