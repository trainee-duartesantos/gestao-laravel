<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Document $invoice
    ) {}

    public function build()
    {
        // gerar PDF
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice
        ]);

        return $this
            ->subject('Fatura Nº ' . $this->invoice->number)
            ->view('emails.invoice')
            ->attachData(
                $pdf->output(),
                'Fatura_' . $this->invoice->number . '.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
