# Task Checklist: POS Retail (Weeks 18-24)

## Weeks 18-19: Discount & Promotion System
- [x] **Database Migration**: Create `promotions`, `promotion_products`, `coupons` tables
- [x] **Models**: Create `Promotion`, `Coupon` models with relationships
- [ ] **Service Layer**: Implement `PromotionService` for calculation logic
- [ ] **Admin UI**:
    - [ ] List Promotions page
    - [ ] Create/Edit Promotion form (with type selection: %, fixed, buy X get Y)
    - [ ] Coupon management page
- [ ] **POS Integration**:
    - [ ] Auto-apply automatic promotions in Cart
    - [ ] Add "Enter Coupon" button and modal
    - [ ] Display discount amount in receipt preview
- [x] **Deadstock Intelligence (Report Exists)**:
    - [x] Query: `ReportService::getDeadStockReport` (Existing)
    - [x] Dashboard: `admin.reports.dead_stock` (Existing)
    - [ ] **Integration**: Update "Edit / Diskon" link to -> "Create Promotion" with pre-filled data.
- [ ] **Backend**: Update `Transaction` logic to save discount details

## Week 20: Shift Management
- [ ] **Database Migration**: Create `shifts`, `cash_register_sessions`, `cash_movements` tables
- [ ] **Models**: Create `Shift`, `CashRegisterSession`, `CashMovement`
- [ ] **Middleware**: Ensure active session exists before processing sales
- [ ] **UI/UX**:
    - [ ] Login Intercept: "Open Register" modal if no session active
    - [ ] Dashboard Widget: Current shift status linked to User
    - [ ] "Close Register" page with cash counting form
- [ ] **Reporting**:
    - [ ] Z-Report generation (daily sales summary per register)
    - [ ] Shift history view for Admins

## Week 21: Return & Refund Management
- [ ] **Database Migration**: Create `returns`, `return_items` tables
- [ ] **Models**: Create `Return`, `ReturnItem`
- [ ] **UI/UX**:
    - [ ] Transaction History: Add "Return" button on eligible operations
    - [ ] Return Request Form: Select items, quantity, condition, and reason
- [ ] **Logic**:
    - [ ] Stock adjustment service (increment stock if condition=good)
    - [ ] Refund calculation (partial or full)
    - [ ] Create negative `Transaction` record (optional) or link `Return` to `Transaction`

## Week 23: Direct Printing System
- [ ] **Research**: Confirm printer model and browser capabilities
- [ ] **Frontend**:
    - [ ] Create optimized Receipt View (CSS for 58mm/80mm)
    - [ ] JavaScript print trigger (`window.print()`)
    - [ ] (Optional) Integrate QZ Tray or similar for silent printing
- [ ] **Settings**: Add "Printer Configuration" in Settings (Header/Footer text, Logo)

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
