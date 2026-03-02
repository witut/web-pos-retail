<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Faktur - {{ $transaction->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            width: 210mm;
            /* A5 Landscape / Half Letter Width */
            padding: 20px;
            background: white;
            margin: 0 auto;
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .store-name {
            font-size: 20px;
            font-weight: bold;
        }

        .faktur-title {
            font-size: 24px;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 2px 5px;
            vertical-align: top;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items th,
        .items td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .items th {
            font-weight: bold;
            background-color: #f8f8f8;
            text-align: center;
        }

        .items .num,
        .items .qty {
            text-align: center;
            width: 50px;
        }

        .items .price,
        .items .subtotal,
        .items .discount {
            text-align: right;
        }

        .totals-wrapper {
            float: right;
            width: 40%;
        }

        .totals {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 5px;
            text-align: right;
        }

        .totals .label {
            text-align: left;
            font-weight: bold;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            clear: both;
        }

        .signatures {
            width: 100%;
            margin-top: 50px;
            text-align: center;
        }

        .signatures td {
            width: 33%;
            padding-top: 60px;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="store-name">{{ $storeName }}</div>
                <div>{{ $storeAddress }}</div>
                <div>Telp: {{ $storePhone }}</div>
            </td>
            <td width="40%" class="faktur-title">
                INVOICE
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="15%">No. Invoice</td>
            <td width="2%">:</td>
            <td width="33%">{{ $transaction->invoice_number }}</td>
            <td width="15%">Pelanggan</td>
            <td width="2%">:</td>
            <td width="33%">{{ $transaction->customer->name ?? 'Pelanggan Umum' }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $transaction->cashier->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Metode Bayar</td>
            <td>:</td>
            <td>{{ strtoupper($transaction->payment_method) }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="num">No</th>
                <th>Nama Barang</th>
                <th class="qty">Qty</th>
                <th>Satuan</th>
                <th class="price">Harga (Rp)</th>
                <th class="discount">Diskon (Rp)</th>
                <th class="subtotal">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $index => $item)
                @php
                    $parts = explode('|PROMO: ', $item->product_name);
                    $name = current(explode('|', $parts[0]));
                    $promoName = count($parts) > 1 ? $parts[1] : null;
                    
                    $displayName = $item->product->name ?? $name;
                @endphp
                <tr>
                    <td class="num">{{ $index + 1 }}</td>
                    <td>
                        {{ $displayName }}
                        @if($promoName)
                            <br><small style="color: #666;">*** PROMO: {{ $promoName }} ***</small>
                        @elseif($item->discount_amount > 0)
                            <br><small style="color: #666;">Disc. {{ Str::limit($displayName, 10) }}</small>
                        @endif
                    </td>
                    <td class="qty">{{ number_format($item->qty, 0, ',', '.') }}</td>
                    <td>{{ $item->unit_name ?? 'pcs' }}</td>
                    <td class="price">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="discount">
                        @if($item->discount_amount > 0)
                            {{ number_format($item->discount_amount, 0, ',', '.') }}
                        @else
                            0
                        @endif
                    </td>
                    <td class="subtotal">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals-wrapper">
            <table class="totals">
                <tr>
                    <td class="label">Subtotal</td>
                    <td>:</td>
                    <td>{{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                </tr>

                @php 
                    $totalDiscount = $transaction->discount_amount + $transaction->points_discount_amount + $transaction->coupon_discount_amount; 
                @endphp
                
                @if($totalDiscount > 0)
                                    <tr>
                                        <td class="label">Total Diskon</td>
                                        <td>:</td>
                                        <td>({{ number_format($totalDiscount, 0, ',', '.') }})</td>
                    </tr>
                @endif
                
                @if($transaction->tax_amount > 0)
                                    <tr>
                                        <td class="label">Pajak ({{ $taxType }})</td>
                                        <td>:</td>
                                        <td>{{ number_format($transaction->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                
                <tr>
                    <td class="label" style="font-size:16px;">TOTAL</td>

                                        <td>:</td>
                    <td style="font-size:16px; font-weight:bold;">{{ number_format($transaction->total, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Tunai / Bayar</td>
                    <td>:</td>
                    <td>{{ number_format($transaction->amount_paid, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Kembali</td>
                    <td>:</td>
                    <td>{{ number_format($transaction->amount_paid - $transaction->total, 0, ',', '.') }}</td>
                </tr>
    </table>
        </div>
        
        <div style="float: left; width: 50%;">
            <p><strong>Catatan:</strong></p>
            <p style="white-space: pre-line">{{ $receiptFooter }}</p>
        </div>
    </div>

    <table class="signatures">
        <tr>
            <td>
                ( .................... )<br>
                Penerima / Pembeli
            </td>
            <td>
                ( .................... )<br>
                Hormat Kami
            </td>
        </tr>
    </table>

    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button id="btnPrint" style="padding:10px 30px;font-size:14px;cursor:pointer;background:#1e293b;color:white;border:none;border-radius:5px;">Cetak Faktur</button>
        <button onclick="window.close()" style="padding:10px 30px;font-size:14px;cursor:pointer;background:#e5e7eb;color:#374151;border:none;border-radius:5px;margin-left:10px;">Tutup</button>
    </div>

    <script>
        const printerSettings = @json($printerSettings ?? ['type' => 'browser']);
        const transactionId = {{ $transaction->id }};
        const printBtn = document.getElementById('btnPrint');

        async function executePrint() {
            printBtn.disabled = true;
            printBtn.innerText = 'Mencetak...';

            try {
                if (printerSettings.type === 'escpos') {
                    const res = await fetch(`/pos/transactions/${transactionId}/print-payload`);
                    const data = await res.json();
                    if (data.success) {
                        const printRes = await fetch(printerSettings.server_url + '/print', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(data.data)
                        });
                        if (!printRes.ok) throw new Error("Print Server tidak merespon.");
                        setTimeout(() => window.close(), 1000);
                    } else {
                        throw new Error("Gagal mengambil data faktur");
                    }
                } else {
                    window.print();
                }
            } catch (e) {
                console.error("Print Error:", e);
                alert("Gagal mencetak faktur: " + e.message);
            } finally {
                printBtn.disabled = false;
                printBtn.innerText = 'Cetak Faktur';
            }
        }

        printBtn.addEventListener('click', executePrint);

        window.onload = function () {
            if (printerSettings.type === 'escpos') {
                executePrint();
            }
        }
    </script>

</body>
</html>
