<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>Olá {{ $invoice->entity->name }},</p>

    <p>Segue em anexo a fatura nº <strong>{{ $invoice->number }}</strong>.</p>

    <p>
        Total: <strong>{{ number_format($invoice->total, 2) }} €</strong><br>
        Data: {{ $invoice->date->format('d/m/Y') }}
    </p>

    <p>
        Qualquer questão, estamos disponíveis.
    </p>

    <p>
        Cumprimentos,<br>
        <strong>{{ config('app.name') }}</strong>
    </p>
</body>
</html>
