<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
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

        .text-center {
            text-align: center;
        }

        .badge {
            font-size: 9px;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin:0;">RESTAURANT MANAGEMENT SYSTEM</h2>
        <h3 style="margin:5px 0;">Orders Summary Report</h3>
        <p style="margin:0; font-size: 10px;">Date Range: {{ $startDate }} to {{ $endDate }} | Generated:
            {{ date('Y-m-d H:i') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Order No</th>
                <th>Date & Time</th>
                <th>Type</th>
                <th>Waiter</th>
                <th>Status</th>
                <th class="text-end">Subtotal</th>
                <th class="text-end">VAT (18%)</th>
                <th class="text-end">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $ord)
                <tr>
                    <td>#{{ $ord->order_no }}</td>
                    <td>{{ $ord->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $ord->order_type }}</td>
                    <td>{{ $ord->waiter->name ?? 'N/A' }}</td>
                    <td>{{ $ord->status }}</td>
                    <td class="text-end">Rs {{ number_format($ord->sub_total, 2) }}</td>
                    <td class="text-end">Rs {{ number_format($ord->vat_amount, 2) }}</td>
                    <td class="text-end">Rs {{ number_format($ord->grand_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #fafafa;">
                <td colspan="7" class="text-end">Total Revenue (Paid):</td>
                <td class="text-end">Rs {{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
