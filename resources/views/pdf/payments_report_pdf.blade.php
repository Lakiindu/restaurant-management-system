<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipts Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .table th {
            background-color: #f4f4f4;
        }

        .text-end {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin:0;">RESTAURANT MANAGEMENT SYSTEM</h2>
        <h3 style="margin:5px 0;">Payment Receipts Collection Report</h3>
        <p style="margin:0; font-size: 10px;">Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Order No</th>
                <th>Date</th>
                <th>Method</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Change</th>
                <th>Staff</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $pmt)
                <tr>
                    <td>#PAY-{{ $pmt->id }}</td>
                    <td>#{{ $pmt->order->order_no ?? 'N/A' }}</td>
                    <td>{{ $pmt->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $pmt->payment_method }}</td>
                    <td class="text-end">Rs {{ number_format($pmt->amount, 2) }}</td>
                    <td class="text-end">Rs {{ number_format($pmt->change_given, 2) }}</td>
                    <td>{{ $pmt->order->waiter->name ?? 'System' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="4" class="text-end">Total Collected:</td>
                <td class="text-end">Rs {{ number_format($payments->sum('amount'), 2) }}</td>
                <td class="text-end">Rs {{ number_format($payments->sum('change_given'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
