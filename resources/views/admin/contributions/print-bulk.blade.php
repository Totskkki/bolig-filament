<!DOCTYPE html>
<html>
<head>
    <title>Bulk Receipts</title>
    <style>
        @font-face {
            font-family: "Courier New", monospace;
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

       .logo {
            text-align: center;
        }

        .logo img {
            height: 50px;
            margin-bottom: 0.5rem;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        ">
            🖨️ Print Receipts
        </button>
    </div>

    @foreach ($receipts as $receipt)
        <div class="receipt">
            <div class="logo">
                {{-- <img src="{{ public_path('images/android-chrome-192x192.png') }}" alt="BOLIG Logo"> --}}
                    <img src="{{ asset('images/astiglogo.png') }}" alt="BOLIG Logo" onerror="this.style.display='none'">
            </div>
            <div class="title">Bread of Life in God (BOLIG)</div>
            <div style="text-align: center; font-size: 12px;">Contribution Receipt</div>

            <div style="margin-top: 10px;">
                <strong>Payer:</strong><br>{{ $receipt['payer']->name->full_name }}
            </div>

            <div>
                <strong>Date:</strong><br>{{ now()->format('F j, Y - g:i A') }}
            </div>

            <div class="line"></div>
            <div><strong>Paid Contributions:</strong></div>
            <ul style="padding-left: 15px;">
                @foreach ($receipt['contributions'] as $c)
                    <li>
                        For <strong>{{ $c->deceased->member->name->full_name ?? 'N/A' }}</strong> -----
                        &#8369;{{ number_format($c->amount, 2) }}
                    </li>
                @endforeach
            </ul>

           <div class="line"></div>
            <div><strong>Total Paid:</strong> &#8369;{{ number_format($receipt['total'], 2) }}</div>
            <div class="batch">Batch ID: {{ $receipt['contributions']->first()->payment_batch ?? 'N/A' }}</div>
            <div class="footer">Thank you for your support!</div>

          <div style="margin-top: 10px; text-align: center;">
    {!! QrCode::encoding('UTF-8')->size(50)->generate($receipt['qr']) !!}
</div>


        </div>
    @endforeach
</body>
</html>
