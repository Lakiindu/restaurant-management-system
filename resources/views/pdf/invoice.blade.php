<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_no }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .details-table {
            width: 100%;
            margin-bottom: 30px;
        }

        .details-table td {
            padding: 5px;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #f4f4f4;
            border-bottom: 2px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .items-table td {
            border-bottom: 1px solid #eee;
            padding: 10px;
        }

        .items-table th.right,
        .items-table td.right {
            text-align: right;
        }

        .totals-table {
            width: 50%;
            float: right;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .totals-table tr.bold td {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
        }

        .payments {
            margin-top: 50px;
            clear: both;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #888;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>THE GRAND RESTAURANT</h1>
        <p>123 Culinary Avenue, Food City</p>
        <p>Tel: +94 11 234 5678 | VAT Reg No: 987654321V</p>
    </div>

    <table class="details-table">
        <tr>
            <td>
                <strong>Invoice No:</strong> {{ $order->order_no }}<br>
                <strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}<br>
                <strong>Status:</strong> {{ $order->status }}
            </td>
            <td style="text-align: right;">
                <strong>Table:</strong> {{ $order->table ? $order->table->name : 'Takeaway' }}<br>
                <strong>Server:</strong> {{ $order->waiter ? $order->waiter->name : 'N/A' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Notes</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->menuItem->name }}</td>
                    <td style="font-size: 12px; color: #777;">{{ $item->notes }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">Rs {{ number_format($item->price, 2) }}</td>
                    <td class="right">Rs {{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal</td>
            <td style="text-align: right;">Rs {{ number_format($order->sub_total, 2) }}</td>
        </tr>
        <tr>
            <td>VAT (18%)</td>
            <td style="text-align: right;">Rs {{ number_format($order->vat_amount, 2) }}</td>
        </tr>
        @if ($order->discount_amount > 0)
            <tr>
                <td>Discount</td>
                <td style="text-align: right;">- Rs {{ number_format($order->discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="bold">
            <td>GRAND TOTAL</td>
            <td style="text-align: right;">Rs {{ number_format($order->grand_total, 2) }}</td>
        </tr>
    </table>

    <div class="payments">
        <strong>Payment Breakdown:</strong><br>
        @foreach ($order->payments as $payment)
            <small>{{ $payment->payment_method }}: Rs {{ number_format($payment->amount, 2) }}</small><br>
        @endforeach
    </div>

    <div class="footer">
        Thank you for your business!<br>
        Powered by Restaurant POS
    </div>

</body>

</html>
