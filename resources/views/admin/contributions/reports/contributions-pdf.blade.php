<!DOCTYPE html>
<html>
<head>
    <title>Contribution Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Contribution Report</h2>
    <p><strong>From:</strong> {{ $from }} | <strong>To:</strong> {{ $to }}</p>

    <table>
        <thead>
            <tr>
                <th>Payer</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Batch ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contributions as $c)
                <tr>
                    <td>{{ $c->payer->name->full_name ?? 'N/A' }}</td>
                    <td>₱{{ number_format($c->amount, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($c->payment_date)->format('Y-m-d') }}</td>
                    <td>{{ $c->payment_batch }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="text-align:right">Total: ₱{{ number_format($total, 2) }}</h3>
</body>
</html>
