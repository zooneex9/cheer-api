<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
        }

        .logo {
            margin-bottom: 20px;
            text-align: center;
        }

        .logo img {
            max-width: 200px;
        }

        .quotation-table {
            width: 100%;
            border-collapse: collapse;
        }

        .quotation-table th,
        .quotation-table td {
            padding: 10px;
            border: 1px solid #ccc;
        }

        .quotation-table th {
            background-color: #f0f0f0;
            text-align: left;
        }

        .quotation-table td:last-child {
            text-align: right;
        }

        .total {
            text-align: right;
            margin-top: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img style="width: 200px; height: 200px;" src="https://www.aguamayoreo.com/logo.png" alt="Logo">
    </div>
    <div class="header">
        <h1>Cotización</h1>
    </div>
    <table class="quotation-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr>
                <td>{{ $product['sku'] }}</td>
                <td>{{ $product['qty'] }}</td>
                <td>${{ number_format(($product['price']), 2, '.', ','); }} MXN</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total: ${{ $total }} MXN
    </div>
</body>
</html>
