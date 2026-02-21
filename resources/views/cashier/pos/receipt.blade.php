<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk - {{ $transaction->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width:
                {{ \App\Models\Setting::get('printer.paper_width', '80') }}
                mm;
            padding: 10px;
            background: white;
            margin: 0 auto;
            /* Center receipt */
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .store-info {
            font-size: 10px;
        }

        .invoice-info {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .invoice-info table {
            width: 100%;
        }

        .invoice-info td {
            padding: 2px 0;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items th,
        .items td {
            padding: 3px 0;
            text-align: left;
        }

        .items th {
            border-bottom: 1px dashed #000;
            font-size: 10px;
        }

        .items .qty {
            text-align: center;
            width: 40px;
        }

        .items .price,
        .items .subtotal {
            text-align: right;
        }

        .items .product-name {
            font-size: 11px;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .totals {
            width: 100%;
        }

        .totals td {
            padding: 3px 0;
        }

        .totals .label {
            text-align: left;
        }

        .totals .value {
            text-align: right;
            font-weight: bold;
        }

        .totals .grand-total {
            font-size: 14px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .payment-info {
            margin-top: 10px;
            font-size: 11px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        .barcode {
            text-align: center;
            margin-top: 10px;
            font-family: 'Libre Barcode 39', cursive;
            font-size: 36px;
        }

        @media print {
            body {
                width: 80mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">{{ $storeName ?? 'TOKO RETAIL' }}</div>
            <div class="store-info">
                {{ $storeAddress ?? 'Jl. Contoh No. 123' }}<br>
                Telp: {{ $storePhone ?? '-' }}
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <table>
                <tr>
                    <td>No</td>
                    <td>: {{ $transaction->invoice_number }}</td>
                </tr>
                <tr>
                    <td>Tgl</td>
                    <td>: {{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>: {{ $transaction->cashier->name ?? '-' }}</td>
                </tr>
                @if ($transaction->customer)
                    <tr>
                        <td>Pelanggan</td>
                        <td>: {{ $transaction->customer->name }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="separator"></div>

        <!-- Items -->
        <table class="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="qty">Qty</th>
                    <th class="price">Harga</th>
                    <th class="subtotal">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->items as $item)
                    <tr>
                        <td class="product-name">
                            {{ Str::limit($item->product->name ?? $item->product_name, 15) }}
                        </td>
                        <td class="qty">{{ number_format($item->qty) }}</td>
                        <td class="price">{{ number_format($item->unit_price) }}</td>
                        <td class="subtotal">{{ number_format($item->subtotal + $item->discount_amount) }}</td>
                        <!-- Gross Subtotal -->
                    </tr>
                    @if($item->discount_amount > 0)
                        <tr>
                            <td colspan="3" style="text-align: right; font-size: 10px; color: #555;">Disc.
                                {{ Str::limit($item->product->name ?? $item->product_name, 10) }}:
                            </td>
                            <td class="subtotal" style="font-size: 10px; color: #555;">
                                -{{ number_format($item->discount_amount) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="separator"></div>

        <!-- Totals -->
        <table class="totals">
            @php
                $globalDiscount = $transaction->coupon_discount_amount + $transaction->points_discount_amount + $transaction->discount_amount;
            @endphp

            @if ($taxType === 'inclusive')
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">Rp {{ number_format($transaction->subtotal) }}</td>
                </tr>
                @if ($globalDiscount > 0)
                    <tr>
                        <td class="label">Diskon</td>
                        <td class="value">-Rp {{ number_format($globalDiscount) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td class="label"><strong>Total</strong></td>
                    <td class="value"><strong>Rp {{ number_format($transaction->total) }}</strong></td>
                </tr>
                @if ($transaction->tax_amount > 0)
                    <tr>
                        <td colspan="2" style="text-align: right; font-size: 10px; color: #555; padding-top: 2px;">
                            Sudah termasuk ppn Rp {{ number_format($transaction->tax_amount) }}
                        </td>
                    </tr>
                @endif
            @else
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">Rp {{ number_format($transaction->subtotal) }}</td>
                </tr>
                @if ($globalDiscount > 0)
                    <tr>
                        <td class="label">Diskon</td>
                        <td class="value">-Rp {{ number_format($globalDiscount) }}</td>
                    </tr>
                @endif
                @if ($transaction->tax_amount > 0)
                    <tr>
                        <td class="label">PPN</td>
                        <td class="value">Rp {{ number_format($transaction->tax_amount) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td class="label"><strong>Total</strong></td>
                    <td class="value"><strong>Rp {{ number_format($transaction->total) }}</strong></td>
                </tr>
            @endif
        </table>

        <!-- Payment Info -->
        <div class="payment-info">
            <table>
                <tr>
                    <td>Bayar ({{ ucfirst($transaction->payment_method) }})</td>
                    <td style="text-align: right">Rp {{ number_format($transaction->amount_paid) }}</td>
                </tr>
                <tr>
                    <td>Kembali</td>
                    <td style="text-align: right">Rp {{ number_format($transaction->change_amount) }}</td>
                </tr>
            </table>
        </div>

        @if ($transaction->customer)
            <div class="separator"></div>
            <!-- Loyalty Info -->
            <div class="invoice-info">
                <strong>Info Poin Member</strong>
                <table>
                    @if ($transaction->points_earned > 0)
                        <tr>
                            <td>Poin Didapat</td>
                            <td style="text-align: right">+{{ number_format($transaction->points_earned) }}</td>
                        </tr>
                    @endif
                    @if ($transaction->points_redeemed > 0)
                        <tr>
                            <td>Poin Ditukar</td>
                            <td style="text-align: right">-{{ number_format($transaction->points_redeemed) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Sisa Poin</td>
                        <td style="text-align: right">{{ number_format($transaction->customer->points_balance) }}</td>
                    </tr>
                </table>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>{{ $receiptFooter ?? 'Terima kasih atas kunjungan Anda!' }}</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        </div>
    </div>

    <!-- Print Button (no-print) -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()"
            style="padding: 10px 30px; font-size: 14px; cursor: pointer; background: #1e293b; color: white; border: none; border-radius: 5px;">
            Cetak Struk
        </button>
        <button onclick="window.close()"
            style="padding: 10px 30px; font-size: 14px; cursor: pointer; background: #e5e7eb; color: #374151; border: none; border-radius: 5px; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <script>
        // Auto print
        window.onload = function () {
            // Uncomment the line below to auto-print
            // window.print();
        }
    </script>
</body>

</html>