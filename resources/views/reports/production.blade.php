<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documento di Produzione</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { text-align: center; }
        .info { margin-bottom: 20px; }
        .taglie { margin-top: 20px; }
        .taglie li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Documento di Produzione</h1>
    <div class="info">
        <p><strong>Nome Cliente:</strong> {{ $order['nome_cliente'] }}</p>
        <p><strong>Data Consegna:</strong> {{ $order['data_consegna'] }}</p>
        <p><strong>Destinazione Merce:</strong> {{ $order['destinazione_merce'] }}</p>
    </div>
    <div class="taglie">
        <h2>Quantità per Taglia</h2>
        <ul>
            @foreach ($order['quantita_per_taglia'] as $taglia => $quantita)
                <li>{{ $taglia }}: {{ $quantita }}</li>
            @endforeach
        </ul>
    </div>
</body>
</html>
