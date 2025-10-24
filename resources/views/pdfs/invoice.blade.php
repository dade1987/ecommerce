<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fattura {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 40%;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .customer-info {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .totals {
            float: right;
            width: 300px;
        }
        .total-row {
            padding: 5px 0;
        }
        .total-row.grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .footer {
            margin-top: 50px;
            font-size: 10px;
            color: #666;
        }
        .product-twins {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h1>{{ $company['name'] }}</h1>
            <p>{{ $company['address'] }}</p>
            <p>P.IVA: {{ $company['vat_number'] }}</p>
            <p>Cod. Fiscale: {{ $company['fiscal_code'] }}</p>
            <p>Tel: {{ $company['phone'] }} | Email: {{ $company['email'] }}</p>
        </div>
        <div class="invoice-info">
            <h2>FATTURA</h2>
            <p><strong>Numero:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Data:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
            <p><strong>Scadenza:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
            <p><strong>Stato:</strong> {{ ucfirst($invoice->status) }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="customer-info">
        <h3>Cliente:</h3>
        <p><strong>{{ $invoice->customer->name }}</strong></p>
        @if($invoice->customer->address)
            <p>{{ $invoice->customer->address }}</p>
        @endif
        @if($invoice->customer->email)
            <p>Email: {{ $invoice->customer->email }}</p>
        @endif
        @if($invoice->customer->phone)
            <p>Tel: {{ $invoice->customer->phone }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Prodotto</th>
                <th>UUID ProductTwin</th>
                <th>Quantità</th>
                <th>Prezzo Unitario</th>
                <th>Totale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>
                    {{ $item->internalProduct->name }}
                    <div class="product-twins">
                        @foreach($item->productTwins as $twin)
                            <div>• {{ $twin->uuid }}</div>
                        @endforeach
                    </div>
                </td>
                <td>
                    @foreach($item->productTwins as $twin)
                        <div>{{ $twin->uuid }}</div>
                    @endforeach
                </td>
                <td>{{ $item->quantity }}</td>
                <td>€ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td>€ {{ number_format($item->total_price, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <strong>Subtotale:</strong> € {{ number_format($invoice->subtotal, 2, ',', '.') }}
        </div>
        <div class="total-row">
            <strong>IVA (22%):</strong> € {{ number_format($invoice->tax_amount, 2, ',', '.') }}
        </div>
        <div class="total-row grand-total">
            <strong>TOTALE:</strong> € {{ number_format($invoice->total_amount, 2, ',', '.') }}
        </div>
    </div>

    <div class="clear"></div>

    @if($invoice->notes)
    <div style="margin-top: 30px;">
        <h4>Note:</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Fattura generata automaticamente dal sistema di tracciabilità</p>
        <p>Data generazione: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html> 