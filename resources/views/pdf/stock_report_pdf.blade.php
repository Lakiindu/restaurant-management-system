<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Stock & Consumption Report</title>
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

        h4 {
            margin: 15px 0 5px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin:0;">RESTAURANT MANAGEMENT SYSTEM</h2>
        <h3 style="margin:5px 0;">Stock Valuation & Consumption Report</h3>
        <p style="margin:0; font-size: 10px;">Generated: {{ date('Y-m-d H:i') }} | Period: {{ $startDate }} to
            {{ $endDate }}</p>
    </div>

    <h4>Section A: Inventory Stock Status</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Unit</th>
                <th class="text-end">Cost Price</th>
                <th class="text-end">Current Stock</th>
                <th class="text-end">Reorder Level</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $itm)
                <tr>
                    <td>{{ $itm->name }}</td>
                    <td>{{ $itm->category_name ?? 'N/A' }}</td>
                    <td>{{ $itm->unit_measure }}</td>
                    <td class="text-end">Rs {{ number_format($itm->cost_price, 2) }}</td>
                    <td class="text-end">{{ number_format($itm->current_stock, 2) }}</td>
                    <td class="text-end">{{ number_format($itm->reorder_level, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Section B: Period Ingredient Consumption</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Ingredient Name</th>
                <th>Unit Measure</th>
                <th class="text-end">Total Consumed</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($consumption as $c)
                <tr>
                    <td>{{ $c->item_name }}</td>
                    <td>{{ $c->unit_measure }}</td>
                    <td class="text-end">{{ number_format($c->total_consumed, 2) }} {{ $c->unit_measure }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
