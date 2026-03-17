<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label Barcode</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        /* ============================================
           SCREEN STYLES (Preview before print)
        ============================================ */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }

        .screen-controls {
            background: #1e3a5f;
            color: white;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .screen-controls h1 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .screen-controls .controls-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .control-group label {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .control-group select {
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            font-size: 0.85rem;
            background: rgba(255,255,255,0.15);
            color: white;
            cursor: pointer;
        }

        .control-group select option { color: #1e293b; background: white; }

        .btn-print {
            background: #f59e0b;
            color: #1e293b;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-print:hover { background: #d97706; }

        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back:hover { background: rgba(255,255,255,0.25); }

        .preview-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .preview-section h2 {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .product-section {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px dashed #e5e7eb;
        }

        .product-section:last-child { border-bottom: none; }

        .product-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .product-name-badge {
            font-weight: 600;
            color: #1e3a5f;
            font-size: 0.95rem;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #374151;
        }

        .qty-control input[type=number] {
            width: 60px;
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* ============================================
           LABEL GRID (screen preview)
        ============================================ */
        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-start;
        }

        /* ============================================
           SINGLE LABEL STYLES (screen + print)
        ============================================ */
        .label-card {
            border: 1px solid #9ca3af;
            border-radius: 4px;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4px 6px 6px;
            text-align: center;
            overflow: hidden;
        }

        /* Size variants (screen) */
        .label-card.size-small  { width: 114px; min-height: 76px; }
        .label-card.size-medium { width: 151px; min-height: 95px; }
        .label-card.size-large  { width: 189px; min-height: 114px; }

        .label-product-name {
            font-weight: 700;
            line-height: 1.2;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .label-card.size-small  .label-product-name { font-size: 7px; margin-bottom: 2px; }
        .label-card.size-medium .label-product-name { font-size: 9px; margin-bottom: 3px; }
        .label-card.size-large  .label-product-name { font-size: 11px; margin-bottom: 4px; }

        .label-barcode svg { display: block; }
        .label-card.size-small  .label-barcode svg { width: 100px !important; height: 28px !important; }
        .label-card.size-medium .label-barcode svg { width: 130px !important; height: 36px !important; }
        .label-card.size-large  .label-barcode svg { width: 165px !important; height: 44px !important; }

        .label-price {
            font-weight: 800;
            color: #1e3a5f;
            margin-top: 2px;
        }

        .label-card.size-small  .label-price { font-size: 8px; }
        .label-card.size-medium .label-price { font-size: 10px; }
        .label-card.size-large  .label-price { font-size: 13px; }

        .label-sku {
            color: #6b7280;
        }

        .label-card.size-small  .label-sku { font-size: 6px; }
        .label-card.size-medium .label-sku { font-size: 7px; }
        .label-card.size-large  .label-sku { font-size: 8px; }

        /* ============================================
           PRINT STYLES
        ============================================ */
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .screen-controls,
            .preview-section > h2,
            .product-header {
                display: none !important;
            }

            .preview-section {
                box-shadow: none;
                border-radius: 0;
                padding: 4mm;
                background: white;
            }

            .product-section {
                border: none;
                margin-bottom: 0;
                padding-bottom: 0;
            }

            .labels-grid {
                gap: 2mm;
            }

            .label-card {
                page-break-inside: avoid;
                border-color: #666;
            }

            /* Physical sizes */
            .label-card.size-small  { width: 38mm; min-height: 25mm; }
            .label-card.size-medium { width: 50mm; min-height: 30mm; }
            .label-card.size-large  { width: 63mm; min-height: 38mm; }

            .label-card.size-small  .label-barcode svg { width: 34mm !important; height: 10mm !important; }
            .label-card.size-medium .label-barcode svg { width: 44mm !important; height: 13mm !important; }
            .label-card.size-large  .label-barcode svg { width: 58mm !important; height: 17mm !important; }
        }
    </style>
</head>
<body x-data="labelPrint()" x-init="init()">

    {{-- ===== SCREEN CONTROLS (hidden on print) ===== --}}
    <div class="screen-controls">
        <div>
            <h1>🏷️ Preview Label Barcode</h1>
            <p style="font-size:0.8rem;opacity:0.7;margin-top:2px;">
                Total: <strong id="total-labels-count">0</strong> label akan dicetak
            </p>
        </div>
        <div class="controls-right">
            <div class="control-group">
                <label for="labelSize">Ukuran Label:</label>
                <select id="labelSize" onchange="changeLabelSize(this.value)">
                    <option value="small">Kecil (38×25mm)</option>
                    <option value="medium" selected>Sedang (50×30mm)</option>
                    <option value="large">Besar (63×38mm)</option>
                </select>
            </div>
            <a href="{{ url()->previous() }}" class="btn-back">
                ← Kembali
            </a>
            <button class="btn-print" onclick="window.print()">
                🖨️ Print Label
            </button>
        </div>
    </div>

    {{-- ===== LABEL PREVIEW ===== --}}
    <div class="preview-section">
        <h2>Preview Label (klik Print untuk mencetak)</h2>

        @foreach($products as $product)
            @php
                // Determine barcode value: primary barcode → fallback to SKU
                $primaryBarcode = $product->primaryBarcode?->barcode ?? $product->barcodes->first()?->barcode ?? $product->sku;
                $qty = $quantities[$product->id] ?? 1;
                $price = 'Rp ' . number_format($product->selling_price, 0, ',', '.');
            @endphp

            <div class="product-section">
                <div class="product-header">
                    <span class="product-name-badge">
                        {{ $product->name }}
                        <span style="font-weight:400;color:#6b7280;font-size:0.85rem;">({{ $product->sku }})</span>
                    </span>
                    <div class="qty-control">
                        <label>Jumlah label:</label>
                        <input type="number"
                               id="qty-{{ $product->id }}"
                               value="{{ $qty }}"
                               min="1"
                               max="100"
                               onchange="updateQty({{ $product->id }}, this.value)">
                    </div>
                </div>

                <div class="labels-grid" id="grid-{{ $product->id }}">
                    {{-- Labels rendered by JS based on qty --}}
                </div>
            </div>

            <template id="tpl-{{ $product->id }}"
                      data-name="{{ $product->name }}"
                      data-sku="{{ $product->sku }}"
                      data-barcode="{{ $primaryBarcode }}"
                      data-price="{{ $price }}"
                      data-qty="{{ $qty }}">
            </template>
        @endforeach
    </div>

    <script>
        // ============================================
        // Label generation logic
        // ============================================

        let currentSize = 'medium';
        const allProductIds = @json($products->pluck('id'));
        const quantities = @json($quantities);

        function createLabel(name, sku, barcodeValue, price) {
            const card = document.createElement('div');
            card.className = `label-card size-${currentSize}`;

            // Truncate long names
            const maxChars = currentSize === 'small' ? 22 : (currentSize === 'medium' ? 28 : 35);
            const displayName = name.length > maxChars ? name.substring(0, maxChars) + '…' : name;

            card.innerHTML = `
                <div class="label-product-name">${displayName}</div>
                <div class="label-barcode">
                    <svg class="bc-svg"></svg>
                </div>
                <div class="label-price">${price}</div>
                <div class="label-sku">${sku}</div>
            `;

            // Render barcode
            const svg = card.querySelector('.bc-svg');
            try {
                JsBarcode(svg, barcodeValue, {
                    format: 'CODE128',
                    displayValue: true,
                    fontSize: currentSize === 'small' ? 7 : (currentSize === 'medium' ? 9 : 11),
                    margin: 1,
                    width: currentSize === 'small' ? 1 : (currentSize === 'medium' ? 1.2 : 1.5),
                    height: currentSize === 'small' ? 22 : (currentSize === 'medium' ? 28 : 36),
                });
            } catch (e) {
                svg.innerHTML = `<text x="5" y="20" font-size="8" fill="red">Barcode error</text>`;
            }

            return card;
        }

        function renderProduct(productId) {
            const tpl = document.getElementById(`tpl-${productId}`);
            if (!tpl) return;

            const name     = tpl.dataset.name;
            const sku      = tpl.dataset.sku;
            const barcode  = tpl.dataset.barcode;
            const price    = tpl.dataset.price;
            const qty      = parseInt(quantities[productId] || 1);

            const grid = document.getElementById(`grid-${productId}`);
            grid.innerHTML = '';

            for (let i = 0; i < qty; i++) {
                grid.appendChild(createLabel(name, sku, barcode, price));
            }
        }

        function renderAll() {
            allProductIds.forEach(id => renderProduct(id));
            updateTotalCount();
        }

        function updateQty(productId, value) {
            let qty = parseInt(value);
            if (isNaN(qty) || qty < 1) qty = 1;
            if (qty > 100) qty = 100;
            quantities[productId] = qty;
            renderProduct(productId);
            updateTotalCount();
        }

        function changeLabelSize(size) {
            currentSize = size;
            renderAll();
        }

        function updateTotalCount() {
            let total = 0;
            allProductIds.forEach(id => { total += (quantities[id] || 1); });
            document.getElementById('total-labels-count').textContent = total;
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', renderAll);
    </script>
</body>
</html>
