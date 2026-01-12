<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Entity;
use App\Models\Article;
use App\Models\DocumentLine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    /**
     * Página principal de Propostas
     */
    public function page()
    {
        return Inertia::render('Proposals/Index');
    }

    /**
     * Listagem de propostas
     */
    public function index(Request $request)
    {
        return Document::query()
            ->where('type', 'proposal')
            ->with('entity:id,name')
            ->orderByDesc('date')
            ->paginate(10)
            ->through(fn ($p) => [
                'id' => $p->id,
                'number' => $p->number,
                'date' => $p->date->format('Y-m-d'),
                'validity' => $p->due_date?->format('Y-m-d'),
                'client' => $p->entity->name,
                'total' => $p->total,
                'status' => $p->status,
            ]);
    }

    /**
     * Criar proposta (draft)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'entity_id' => ['required', 'exists:entities,id'],
        ]);

        $proposal = DB::transaction(function () use ($data) {
            return Document::create([
                'type' => 'proposal',
                'number' => Document::nextNumber('proposal'),
                'entity_id' => $data['entity_id'],
                'date' => now(),
                'due_date' => now()->addDays(30),
                'status' => 'draft',
            ]);
        });

        return response()->json([
            'id' => $proposal->id,
        ], 201);
    }

    /**
     * Mostrar proposta (para edição)
     */
    public function show(Document $proposal)
    {
        abort_unless($proposal->type === 'proposal', 404);

        return response()->json([
            'id' => $proposal->id,
            'number' => $proposal->number,
            'date' => $proposal->date->format('Y-m-d'),
            'validity' => $proposal->due_date?->format('Y-m-d'),
            'status' => $proposal->status,
            'entity_id' => $proposal->entity_id,
            'lines' => $proposal->lines()->get(),
            'subtotal' => $proposal->subtotal,
            'vat_total' => $proposal->vat_total,
            'total' => $proposal->total,
        ]);
    }

    public function edit(Document $proposal)
    {
        abort_unless($proposal->type === 'proposal', 404);

        return Inertia::render('Proposals/Edit', [
            'proposal' => [
                'id' => $proposal->id,
                'number' => $proposal->number,
                'date' => $proposal->date->format('Y-m-d'),
                'validity' => $proposal->due_date?->format('Y-m-d'),
                'status' => $proposal->status,
                'entity_id' => $proposal->entity_id,
                'subtotal' => $proposal->subtotal,
                'vat_total' => $proposal->vat_total,
                'total' => $proposal->total,
            ],

            // 🔑 ISTO É O QUE FALTAVA
            'articles' => Article::where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'price']),
        ]);
    }

    public function addLine(Request $request, Document $proposal)
    {
        abort_unless($proposal->type === 'proposal', 404);

        $data = $request->validate([
            'article_id' => ['required', 'exists:articles,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $article = Article::with('vatRate')->findOrFail($data['article_id']);

        DocumentLine::makeFromArticle(
            $proposal,
            $article,
            $data['quantity'] ?? 1
        );

        $proposal->recalculateTotals();

        return response()->json([
            'success' => true,
        ]);
    }
}
