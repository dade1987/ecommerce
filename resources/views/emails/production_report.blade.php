<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Produzione</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            color: #333;
        }
        h2, h3 {
            color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Report Produzione - Ordine Cliente</h2>

        <p><strong>Numero Ordine:</strong> {{ $orderData['ordine']['numero_ordine'] ?? 'N/A' }}</p>
        <p><strong>Data Ordine:</strong> {{ $orderData['ordine']['data_ordine'] ?? 'N/A' }}</p>
        <p><strong>Data Consegna:</strong> {{ $orderData['ordine']['data_consegna'] ?? 'N/A' }}</p>

        <h3>Cliente</h3>
        <p><strong>Nome:</strong> {{ $orderData['ordine']['cliente']['nome'] ?? 'N/A' }}</p>
        <p><strong>Indirizzo Fatturazione:</strong> {{ $orderData['ordine']['cliente']['indirizzo_fatturazione'] ?? 'N/A' }}</p>
        <p><strong>Partita IVA:</strong> {{ $orderData['ordine']['cliente']['partita_iva'] ?? 'N/A' }}</p>

        <h3>Destinazione Merce</h3>
        <p><strong>Indirizzo:</strong> {{ $orderData['ordine']['destinazione_merce']['indirizzo'] ?? 'N/A' }}</p>
        <p><strong>Referente:</strong> {{ $orderData['ordine']['destinazione_merce']['referente'] ?? 'N/A' }}</p>

        <h3>Dettagli Articoli</h3>
        <table>
            <thead>
                <tr>
                    <th>Codice Articolo</th>
                    <th>Descrizione</th>
                    <th>Colore</th>
                    <th>35</th>
                    <th>36</th>
                    <th>37</th>
                    <th>38</th>
                    <th>39</th>
                    <th>40</th>
                    <th>41</th>
                    <th>42</th>
                    <th>43</th>
                    <th>44</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderData['ordine']['dettagli_articoli'] as $articolo)
                <tr>
                    <td>{{ $articolo['codice_articolo'] ?? 'N/A' }}</td>
                    <td>{{ $articolo['descrizione'] ?? 'N/A' }}</td>
                    <td>{{ $articolo['colore'] ?? 'N/A' }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['35'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['36'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['37'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['38'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['39'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['40'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['41'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['42'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['43'] ?? 0 }}</td>
                    <td>{{ $articolo['quantita_per_taglia']['44'] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Condizioni di Pagamento</h3>
        <p>{{ $orderData['ordine']['condizioni_pagamento'] ?? 'N/A' }}</p>

        <h3>Modalità di Spedizione</h3>
        <p>{{ $orderData['ordine']['modalita_spedizione'] ?? 'N/A' }}</p>

        <div class="footer">
            <p>Grazie per aver scelto i nostri servizi.</p>
        </div>
    </div>
</body>
</html>
