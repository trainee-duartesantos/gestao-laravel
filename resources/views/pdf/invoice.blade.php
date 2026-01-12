<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans; font-size: 12px; }
        h1 { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
        .totals { margin-top: 20px; width: 40%; float: right; }
    </style>
</head>
<body>

<h1>Fatura Nº {{ $invoice->number }}</h1>

<p>
    <strong>Data:</strong> {{ $invoice->date->format('d/m/Y') }}<br>
    <strong>Cliente:</strong> {{ $invoice->entity->name }}
</p>

<table>
    <thead>
        <tr>
            <th>Descrição</th>
            <th class="right">Qtd</th>
            <th class="right">Preço</th>
            <th class="right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right">{{ number_format($line->unit_price, 2) }} €</td>
                <td class="right">{{ number_format($line->total, 2) }} €</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Subtotal</td>
        <td class="right">{{ number_format($invoice->subtotal, 2) }} €</td>
    </tr>
    <tr>
        <td>IVA</td>
        <td class="right">{{ number_format($invoice->vat_total, 2) }} €</td>
    </tr>
    <tr>
        <th>Total</th>
        <th class="right">{{ number_format($invoice->total, 2) }} €</th>
    </tr>
</table>

</body>
</html>
