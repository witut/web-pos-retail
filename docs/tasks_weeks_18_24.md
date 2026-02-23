# Task Checklist: POS Retail (Weeks 18-24)

## Weeks 18-19: Discount & Promotion System
- [x] **Database Migration**: Create `promotions`, `promotion_products`, `coupons` tables
- [x] **Models**: Create `Promotion`, `Coupon` models with relationships
- [x] **Service Layer**: Implement `PromotionService` for calculation logic
- [x] **Admin UI**:
    - [x] List Promotions page
    - [x] Create/Edit Promotion form (with type selection: %, fixed, buy X get Y)
    - [x] Coupon management page
- [x] **POS Integration**:
    - [x] Auto-apply automatic promotions in Cart
    - [x] Add "Enter Coupon" button and modal
    - [x] Display discount amount in receipt preview
- [x] **Deadstock Intelligence (Report Exists)**:
    - [x] Query: `ReportService::getDeadStockReport` (Existing)
    - [x] Dashboard: `admin.reports.dead_stock` (Existing)
    - [x] **Integration**: Update "Edit / Diskon" link to -> "Create Promotion" with pre-filled data.
- [x] **Backend**: Update `Transaction` logic to save discount details

## Week 20: Shift Management
- [x] **Database Migration**: Create `shifts`, `cash_register_sessions`, `cash_movements` tables
- [x] **Models**: Create `Shift`, `CashRegisterSession`, `CashMovement`
- [x] **Middleware**: Ensure active session exists before processing sales
- [x] **UI/UX**:
    - [x] Login Intercept: "Open Register" modal if no session active
    - [x] Dashboard Widget: Current shift status linked to User
    - [x] "Close Register" page with cash counting form
- [x] **Reporting**:
    - [x] Z-Report generation (daily sales summary per register)
    - [x] Shift history view for Admins

## Week 21: Return & Refund Management
- [x] **Database Migration**: Create `returns`, `return_items` tables
- [x] **Models**: Create `ProductReturn`, `ProductReturnItem`
- [x] **UI/UX**:
    - [x] Transaction History: Add "Return" button on eligible operations
    - [x] Return Request Form: Select items, quantity, condition, and reason
- [x] **Logic**:
    - [x] Stock adjustment service (increment stock if condition=good)
    - [x] Refund calculation (partial or full)
    - [x] Create negative `Transaction` record (optional) or link `Return` to `Transaction`

## Hold Transaction Feature (New Request)
- [ ] **Database**: Create `held_transactions` and `held_transaction_items` (or similar strategy).
- [ ] **Backend API**:
    - [ ] Endpoint to save current cart as "Held".
    - [ ] Endpoint to fetch list of "Held" transactions for the active session.
    - [ ] Endpoint to resume (load) a held transaction and delete it from hold.
- [ ] **Frontend (POS)**:
    - [ ] Add "Hold Transaksi" button.
    - [ ] Add "Daftar Hold" modal to view and resume held transactions.

## Week 23: Direct Printing System
- [x] **Research**: Confirm printer model and browser capabilities
- [x] **Frontend**:
    - [x] Create optimized Receipt View (CSS for 58mm/80mm)
    - [x] JavaScript print trigger (`window.print()`)
    - [x] (Optional) Integrate QZ Tray or similar for silent printing
- [x] **Settings**: Add "Printer Configuration" in Settings (Header/Footer text, Logo)

## Week 24: Testing, Documentation & Polish
- [ ] **Testing**:
    - [ ] Test Promotion calculation edge cases
    - [ ] Test Shift open/close variances
    - [ ] Test Return stock updates
- [ ] **Documentation**:
    - [ ] Update `README.md`
    - [ ] Create `UserManual.md` for cashiers
- [ ] **Polish**:
    - [ ] Fix UI glitches
    - [ ] Optimize database queries
