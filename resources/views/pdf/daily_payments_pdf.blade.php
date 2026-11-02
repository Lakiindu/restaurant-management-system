<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daily Sales Summary - {{ $date }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #222;
            margin: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header p {
            margin: 3px 0;
            color: #555;
        }

        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .kpi-table td {
            border: 1px solid #ccc;
            padding: 10px;
            background: #fdfdfd;
            text-align: center;
            width: 20%;
        }

        .kpi-table td small {
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .kpi-table td h3 {
            margin: 4px 0 0 0;
            font-size: 14px;
            color: #111;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #e9ecef;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        .items-table td {
            border: 1px solid #eee;
            padding: 8px;
        }

        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .right {
            text-align: right;
        }

        .signatures {
            margin-top: 50px;
            width: 100%;
        }

        .signatures td {
            width: 50%;
            padding-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #444;
        }

        .line {
            border-top: 1px solid #777;
            width: 70%;
            margin: 0 auto 5px auto;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>THE GRAND RESTAURANT</h1>
        <p>End-of-Day Financial Closing Report (Z-Report)</p>
        <p><strong>Report Date:</strong> {{ \Carbon\Carbon::parse($date)->format('F d, Y') }} |
            <strong>Generated:</strong> {{ now()->format('Y-m-d h:i A') }}</p>
    </div>

    <!-- KPI Summary Grid -->
    <table class="kpi-table">
        <tr>
            <td>
                <small>Total Orders</small>
                <h3>{{ $totalOrders }}</h3>
            </td>
            <td>
                <small>Cash Collected</small>
                <h3 style="color: #198754;">Rs {{ number_format($cashTotal, 2) }}</h3>
            </td>
            <td>
                <small>Card Payments</small>
                <h3 style="color: #0dcaf0;">Rs {{ number_format($cardTotal, 2) }}</h3>
            </td>
            <td>
                <small>Online / Digital</small>
                <h3 style="color: #ffc107;">Rs {{ number_format($onlineTotal, 2) }}</h3>
            </td>
            <td>
                <small>Total Revenue</small>
                <h3 style="color: #0d6efd;">Rs {{ number_format($totalSales, 2) }}</h3>
            </td>
        </tr>
    </table>

    <h4 style="margin-bottom: 8px; font-size: 13px;">Paid Transactions Log</h4>
    <table class="items-table">
        <thead>
            <tr>
                <th>Order No</th>
                <th>Type</th>
                <th>Time</th>
                <th>Payment Breakdown</th>
                <th class="right">Subtotal</th>
                <th class="right">VAT (18%)</th>
                <th class="right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><strong>{{ $order->order_no }}</strong></td>
                    <td>{{ $order->order_type }}</td>
                    <td>{{ $order->updated_at->format('h:i A') }}</td>
                    <td>
                        @foreach ($order->payments as $p)
                            <small>{{ $p->payment_method }}: Rs {{ number_format($p->amount, 2) }}</small><br>
                        @endforeach
                    </td>
                    <td class="right">Rs {{ number_format($order->sub_total, 2) }}</td>
                    <td class="right">Rs {{ number_format($order->vat_amount, 2) }}</td>
                    <td class="right"><strong>Rs {{ number_format($order->grand_total, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #888;">No transactions found for this date.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Shift Closing Signatures -->
    <table class="signatures">
        <tr>
            <td>
                <div class="line"></div>
                Cashier / POS Operator Signature
            </td>
            <td>
                <div class="line"></div>
                Manager Approval Signature
            </td>
        </tr>
    </table>

    <div class="footer">
        Confidential Internal Closing Audit Document | Powered by Restaurant POS
    </div>

</body>

</html>
