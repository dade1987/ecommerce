<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .content {
            margin-bottom: 30px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Test Generazione PDF</h1>
    </div>

    <div class="content">
        <h2>{{ $message }}</h2>
        <p>Questo è un test per verificare che la generazione del PDF funzioni correttamente.</p>
        <p><strong>Timestamp:</strong> {{ $timestamp }}</p>
    </div>

    <div class="footer">
        <p>Test completato con successo!</p>
    </div>
</body>
</html> 