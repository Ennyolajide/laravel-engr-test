<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Submitted</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #4CAF50;
        }
        h3 {
            color: #555;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .total {
            font-weight: bold;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Order Submitted</h1>
        <p>You have received a new order from <strong>{{ $order->provider->name }}</strong>.</p>
        <p><strong>HMO Code:</strong> {{ $order->hmo_code }}</p>
        <p><strong>Encounter Date:</strong> {{ $order->encounter_date }}</p>

        <h3>Order Items:</h3>
        <ul>
            @foreach ($order->items as $item)
                <li>
                    <strong>{{ $item->name }}</strong><br>
                    Quantity: {{ $item->quantity }}<br>
                    Unit Price: ${{ number_format($item->unit_price, 2) }}<br>
                    Total: ${{ number_format($item->total, 2) }}
                </li>
            @endforeach
        </ul>

        <h3 class="total">Total Order Amount: ${{ number_format($order->items->sum('total'), 2) }}</h3>

        <p>Thank you for using our service!</p>
    </div>
</body>
</html>
