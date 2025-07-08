<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BOLIG - Payment Receipt</title>
    <style>
        body {
            font-family: "Courier New", monospace;
            padding: 2rem;
            background: #fff;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header img {
            height: 80px;
            margin-bottom: 0.5rem;
        }

        .header h1 {
            margin: 0;
            font-size: 2rem;
            letter-spacing: 2px;
        }

        .header p {
            margin: 0;
            font-size: 0.9rem;
        }

        .summary {
            max-width: 700px;
            margin: 0 auto;
        }

        .row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #aaa;
            padding: 0.5rem 0;
        }

        .details {
            flex: 1;
        }

        .amount {
            min-width: 100px;
            text-align: right;
        }

        .date {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.2rem;
        }

        .total {
            margin-top: 1.5rem;
            text-align: right;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .qr {
            margin-top: 2rem;
            text-align: center;
        }

        .footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.8rem;
            color: #888;
        }

        @media print {
            body {
                background: white;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ asset('images/astiglogo.png') }}" alt="BOLIG Logo" onerror="this.style.display='none'">
        <p>Coordinator Contribution Receipt</p>
    </div>

    <div class="summary">
        @if ($coordinator)
            <p><strong>Coordinator:</strong> {{ $coordinator->full_name }}</p>
        @endif

        @php $grandTotal = 0; @endphp

        @foreach ($payments as $payment)
            <div class="row">
                <div class="details">
                    {{ $payment['member']->full_name }}

                </div>
                <div class="amount">₱{{ number_format($payment['total'], 2) }}</div>
            </div>
            @php $grandTotal += $payment['total']; @endphp
        @endforeach

        <div class="total">Total Paid: ₱{{ number_format($grandTotal, 2) }}</div>
    </div>


    @if (isset($receiptRef))
    <div class="qr">
    <p>Scan for verification</p>
    {!! QrCode::format('svg')->size(50)->generate('Receipt Ref: ' . $receiptRef) !!}
    <div style="font-size: 0.9rem; color: #666;">{{ $receiptRef }}</div>
</div>

@endif


    <div class="footer">
        Printed on {{ now()->format('M d, Y - g:i A') }} — Thank you for your contribution!
    </div>

    <script>window.print();</script>
</body>
</html>
