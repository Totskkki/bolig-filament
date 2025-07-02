<!DOCTYPE html>
<html>
<head>
    <title>BOLIG Contribution Receipt</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/fonts/DejaVuSans.ttf') format('truetype');
        }

        body {
            font-family: 'DejaVu Sans', monospace;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            background: #fff;
        }

        .receipt {
            width: 280px;
            border: 1px dashed #000;
            padding: 15px;
            margin: auto;
        }

        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .info {
            margin-bottom: 8px;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .item {
            margin-bottom: 5px;
        }

        .item-number {
            font-weight: bold;
        }

        .amount {
            text-align: right;
        }

        .total {
            font-weight: bold;
            text-align: right;
            margin-top: 8px;
        }

        .footer {
            text-align: center;
            margin-top: 12px;
            font-style: italic;
            font-size: 11px;
        }

        .batch {
            font-size: 10px;
            text-align: center;
            margin-top: 5px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="title">Bread of Life in God (BOLIG)</div>
        <div class="subtitle">Contribution Receipt</div>

        <div class="info"><strong>Payer:</strong><br>{{ $payer->name->full_name ?? 'N/A' }}</div>
        <div class="info"><strong>Date:</strong><br>{{ now()->format('M d, Y - g:i A') }}</div>

        <div class="line"></div>

        @foreach($contributions as $contribution)
            <div class="item">
                <span class="item-number">{{ $loop->iteration }}.</span>
                {{ $contribution->deceased->member->name->full_name ?? 'N/A' }} -----
                <span>&#8369;{{ number_format($contribution->amount, 2) }}</span>
            </div>
        @endforeach

        <div class="line"></div>

        <div class="total">Total: &#8369;{{ number_format($totalPaid, 2) }}</div>

        <div class="footer">Thank you for your payment!</div>

        @if($contributions->isNotEmpty())
            <div class="batch">Batch ID: {{ $contributions->first()->payment_batch }}</div>
        @endif
        @php
    $qrData = route('contribution.receipt', ['payer' => $payer->memberID, 'batch' => $contributions->first()->payment_batch]);
@endphp

<div style="margin-top: 10px;text-align: center;">
    {!! QrCode::size(50)->generate($qrData) !!}
</div>

    </div>

    <script>
        window.onload = () => {
            window.print();
        };
    </script>
</body>
</html>
