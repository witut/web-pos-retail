# Implementation Plan: POS Retail (Weeks 18-24)

## Overview
This phase focuses on advanced retail features including promotions, shift management, returns, and direct printing.

## 1. Discount & Promotion System (Weeks 18-19)
**Goal**: Enable flexible pricing rules and marketing campaigns.

### Database Schema
- **New Tables**:
    - `promotions`: Defines promotion rules (e.g., Buy X Get Y, % off, fixed amount).
        - `id`, `name`, `type` (percentage, fixed, buy_x_get_y, bundle), `value`, `min_purchase`, `start_date`, `end_date`, `is_active`.
    - `promotion_products`: Links promotions to specific products or categories.
    - `coupons`: For code-based discounts.
        - `code`, `usage_limit`, `used_count`, `expiry_date`.

### Deadstock Management Strategy
- **Identification (EXISTING)**:
    - Report `admin.reports.dead_stock` already exists and identifies products with no sales.
    - Logic uses `ReportService::getDeadStockReport`.
- **Clearance Actions (TODO)**:
    - Update "Edit / Diskon" link in `dead_stock.blade.php` to point to `admin.promotions.create`.
    - Pre-fill Promotion Form:
        - Type: Clearance / Discount.
        - Item: Selected Deadstock Product.
        - Suggestion: Calculate suggested discount based on "Nilai Aset Tertahan".

### Backend Logic
- `PromotionService`: logic to calculate final price.
- `InventoryAnalysisService`: Job to flag potential deadstock.
- Modify `CartController` / `TransactionController` to apply promotions.
- Update `Transaction` model to store `discount_amount` and `promotion_id`.

### Frontend
- UI for managing promotions (admin panel).
- Display active promotions on POS screen.
- Coupon input field in POS cart.

## 2. Shift Management (Week 20)
**Goal**: Track cash register sessions and employee shifts for accountability.

### Database Schema
- **New Tables**:
    - `shifts`: Records employee work hours.
        - `user_id`, `start_time`, `end_time`, `status`.
    - `cash_register_sessions`: Tracks cash flow per register/user.
        - `user_id`, `register_id`, `opening_cash`, `closing_cash`, `expected_cash`, `variance`, `opened_at`, `closed_at`.
    - `cash_movements`: Logs cash in/out (e.g., petty cash, drops).

### Features
- **Open Register**: Prompt for opening amount when logging into POS.
- **Close Register**: End of day report, count cash, calculate variance.
- **X-Report / Z-Report**: Intermediate and final sales reports.

## 3. Return & Refund Management (Week 21)
**Goal**: Handle product returns and refunds securely.

### Database Schema
- **New Tables**:
    - `returns`: Tracks return requests.
        - `transaction_id`, `reason`, `status`, `refund_amount`, `refund_method`.
    - `return_items`: Details of returned products.
        - `return_id`, `product_id`, `quantity`, `condition` (sellable/damaged).

### Logic
- Update Stock: Increase inventory if item is sellable.
- Adjust Calculations: Create negative transaction or separate refund record.
- Validation: Ensure return period is valid and receipt exists.

## 4. Direct Printing System (Week 23)
**Goal**: Send receipts directly to thermal printers without browser print dialog.

### Technical Approach
- **Choice A: Browser Print API (Simple)**
    - Use efficient CSS media queries for thermal paper sizes (58mm/80mm).
    - Auto-trigger print dialog.
- **Choice B: QZ Tray / Local Service (Advanced)**
    - Enables silent printing to specific USB/Network printers.
    - Requires client-side background service.
- **Choice C: Server-Side Printing (Network)**
    - PHP sends ESC/POS commands to network printer IP.

*Recommendation*: Start with optimized Browser Print (Choice A) with an option for ESC/POS raw commands via a local proxy if needed for silent printing.

## 5. Testing, Documentation & Polish (Week 24)
- **Unit/Feature Tests**: Ensure all new modules work as expected.
- **User Manual**: Documentation for staff.
- **Performance**: Optimize database queries for reports.
- **UI/UX Polish**: Smooth transitions, error handling, loading states.
