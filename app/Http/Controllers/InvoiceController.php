<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function page()
    {
        return Inertia::render('Invoices/Index');
    }

    public function index()
    {
        return Document::query()
            ->where('type', 'invoice')
            ->with('entity:id,name')
            ->orderByDesc('date')
            ->paginate(10)
            ->through(fn ($i) => [
                'id' => $i->id,
                'number' => $i->number,
                'date' => $i->date->format('Y-m-d'),
                'client' => $i->entity->name,
                'total' => $i->total,
                'status' => $i->status,
            ]);
    }

    public function show(Document $invoice)
    {
        abort_unless($invoice->type === 'invoice', 404);

        return response()->json([
            'id' => $invoice->id,
            'number' => $invoice->number,
            'date' => $invoice->date->format('Y-m-d'),
            'entity_id' => $invoice->entity_id,
            'status' => $invoice->status,
            'lines' => $invoice->lines,
            'subtotal' => $invoice->subtotal,
            'vat_total' => $invoice->vat_total,
            'total' => $invoice->total,
        ]);
    }

    public function edit(Document $invoice)
    {
        abort_unless($invoice->type === 'invoice', 404);

        return Inertia::render('Invoices/Edit', [
            'invoice' => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'date' => $invoice->date->format('Y-m-d'),
                'status' => $invoice->status,
                'subtotal' => $invoice->subtotal,
                'vat_total' => $invoice->vat_total,
                'total' => $invoice->total,
                'lines' => $invoice->lines,
            ],
        ]);
    }

    public function markPaid(Document $invoice)
    {
        abort_unless($invoice->type === 'invoice', 404);
        abort_unless($invoice->status === 'issued', 403);

        $invoice->update([
            'status' => 'paid',
        ]);

        return response()->json(['success' => true]);
    }

    public function downloadPdf(Document $invoice)
    {
        abort_unless($invoice->type === 'invoice', 404);

        $invoice->load('entity', 'lines');

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice
        ]);

        return $pdf->download(
            'Fatura_' . $invoice->number . '.pdf'
        );
    }
}
