<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo Ordine</title>
</head>

<body>
    <h1>Nuovo Ordine da parte di {{ Auth::user()->email }}</h1>
    <table style="border-collapse: collapse; border: 0;">
        <thead>
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px;">Prodotto</th>
                <th style="border: 1px solid #ddd; padding: 8px;">Prezzo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>{{ $item['name'] }}</strong></td>
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>{{ $item['price'] }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br />
    <p>Grazie per il tuo ordine.</p>
    <p>{{ config('app.name') }}</p>
</body>

</html>
