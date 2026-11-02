<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice Report</title>
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
        <h3 style="margin:5px 0;">Tax & Invoice Summary Report</h3>
        <p style="margin:0; font-size: 10px;">Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Staff</th>
                <th class="text-end">Subtotal</th>
                <th class="text-end">VAT (18%)</th>
                <th class="text-end">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $inv)
                <tr>
                    <td>#{{ $inv->order_no }}</td>
                    <td>{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $inv->waiter->name ?? 'System' }}</td>
                    <td class="text-end">Rs {{ number_format($inv->sub_total, 2) }}</td>
                    <td class="text-end">Rs {{ number_format($inv->vat_amount, 2) }}</td>
                    <td class="text-end">Rs {{ number_format($inv->grand_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="3" class="text-end">Totals:</td>
                <td class="text-end">Rs {{ number_format($invoices->sum('sub_total'), 2) }}</td>
                <td class="text-end">Rs {{ number_format($invoices->sum('vat_amount'), 2) }}</td>
                <td class="text-end">Rs {{ number_format($invoices->sum('grand_total'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
