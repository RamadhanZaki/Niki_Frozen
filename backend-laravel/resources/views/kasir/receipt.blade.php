<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $transaction->invoice_number }} — Niki Frozen</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #111;
            background: #eee;
            margin: 0;
            padding: 24px 0;
        }
        .receipt {
            width: 320px;
            margin: 0 auto;
            background: #fff;
            padding: 20px 18px;
            box-shadow: 0 1px 4px rgba(0,0,0,.2);
        }
        .center { text-align: center; }
        .store-name { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .store-meta { font-size: 11px; color: #444; }
        hr { border: none; border-top: 1px dashed #999; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .meta-row { font-size: 11px; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 6px; }
        td { padding: 2px 0; vertical-align: top; }
        .item-name { padding-top: 6px; }
        .text-end { text-align: right; }
        .totals .row { font-size: 12px; margin-bottom: 3px; }
        .grand-total { font-size: 14px; font-weight: bold; }
        .footer-note { font-size: 11px; margin-top: 14px; }
        .print-actions { max-width: 320px; margin: 14px auto 0; text-align: center; }
        .print-actions button {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 13px;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            background: #0F4C81;
            color: #fff;
            cursor: pointer;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; width: 100%; }
            .print-actions { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt">
        <div class="center">
            <div class="store-name">NIKI FROZEN</div>
            <div class="store-meta">{{ $transaction->branch->name ?? '-' }}</div>
            @if($transaction->branch?->address)
                <div class="store-meta">{{ $transaction->branch->address }}</div>
            @endif
        </div>

        <hr>

        <div class="meta-row row">
            <span>No. Invoice</span>
            <span>{{ $transaction->invoice_number }}</span>
        </div>
        <div class="meta-row row">
            <span>Tanggal</span>
            <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="meta-row row">
            <span>Kasir</span>
            <span>{{ $transaction->user->name ?? '-' }}</span>
        </div>

        <hr>

        <table>
            @foreach($transaction->details as $item)
            <tr>
                <td colspan="3" class="item-name">{{ $item->product->name ?? 'Produk' }}</td>
            </tr>
            <tr>
                <td>{{ $item->qty }} x {{ number_format($item->price_at_sale, 0, ',', '.') }}</td>
                <td></td>
                <td class="text-end">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>

        <hr>

        <div class="totals">
            <div class="row grand-total">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Metode Bayar</span>
                <span>{{ $transaction->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</span>
            </div>
            <div class="row">
                <span>Bayar</span>
                <span>Rp {{ number_format($transaction->payment, 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Kembali</span>
                <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <hr>

        <div class="center footer-note">
            Terima kasih telah berbelanja<br>di Niki Frozen!
        </div>
    </div>

    <div class="print-actions">
        <button onclick="window.print()">🖨️ Cetak Struk</button>
    </div>

</body>
</html>
