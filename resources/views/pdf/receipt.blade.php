<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $order->order_no }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #111;
            margin: 0;
            padding: 0;
            /* Adjust base font size depending on paper size */
            font-size: {{ $size === '80mm' ? '12px' : '14px' }};
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: {{ $size === '80mm' ? '5px' : '40px' }};
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mb-2 {
            margin-bottom: 15px;
        }

        .border-top {
            border-top: 1px dashed #000;
            padding-top: 10px;
            mt-2
        }

        .border-bottom {
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            mb-2
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }

        td {
            padding: 4px 0;
            vertical-align: top;
        }

        /* Restaurant Header */
        .brand-name {
            font-size: {{ $size === '80mm' ? '18px' : '28px' }};
            font-weight: bold;
            margin-bottom: 5px;
        }

        .address {
            font-size: {{ $size === '80mm' ? '10px' : '12px' }};
            color: #444;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Header -->
        <div class="text-center mb-2 border-bottom">
            <div class="brand-name">THE GRAND RESTAURANT</div>
            <div class="address">
                123 Culinary Avenue, Food City<br>
                Tel: +94 11 234 5678<br>
                VAT Reg No: 987654321V
            </div>
        </div>

        <!-- Order Meta -->
        <div class="mb-2">
            <table>
                <tr>
                    <td><strong>Order No:</strong> {{ $order->order_no }}</td>
                    <td class="text-right"><strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Table:</strong> {{ $order->table->table_number ?? 'Walk-in' }}</td>
                    <td class="text-right"><strong>Waiter:</strong> {{ $order->waiter->user_name ?? 'Staff' }}</td>
                </tr>
            </table>
        </div>

        <!-- Line Items -->
        <div class="border-top mb-2">
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 60%;">Item</th>
                        <th style="width: 30%;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="font-bold">{{ $item->quantity }}</td>
                            <td>{{ $item->menuItem->name ?? 'Dish' }}<br>
                                <span style="font-size: 0.85em; color: #555;">@ Rs
                                    {{ number_format($item->price, 2) }}</span>
                            </td>
                            <td class="text-right">Rs {{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="border-top mb-2">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rs {{ number_format($order->sub_total, 2) }}</td>
                </tr>
                @if ($order->discount_amount > 0)
                    <tr>
                        <td>Discount</td>
                        <td class="text-right">- Rs {{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td>VAT (18%)</td>
                    <td class="text-right">Rs {{ number_format($order->vat_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="font-bold" style="font-size: 1.2em; padding-top: 10px;">GRAND TOTAL</td>
                    <td class="font-bold text-right" style="font-size: 1.2em; padding-top: 10px;">Rs
                        {{ number_format($order->grand_total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Payments (Split Bill breakdown) -->
        <div class="border-top mb-2" style="font-size: 0.9em;">
            <strong>Payment Method(s):</strong>
            <table>
                @foreach ($order->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_method }}</td>
                        <td class="text-right">Rs {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @if ($payment->change_given > 0)
                        <tr>
                            <td>Change Given</td>
                            <td class="text-right">Rs {{ number_format($payment->change_given, 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            </table>
        </div>

        <!-- Footer -->
        <div class="border-top text-center" style="padding-top: 15px;">
            <div class="font-bold">Thank you for dining with us!</div>
            <div class="address" style="margin-top: 5px;">Powered by Restaurant POS</div>
        </div>

    </div>
</body>

</html>
