// Customer Component for POS
console.log('LOADING CUSTOMER COMPONENT SCRIPT');
function customerComponent() {
return {
// Customer Data
selectedCustomer: null,
customerSearch: '',
customerResults: [],
customerSearchFocused: false,

// Points Management
pointsToRedeem: 0,
pointsDiscount: 0,
tempPointsDiscount: 0,
redeemAmount: 0,

// Quick Add
showQuickAddModal: false,
newCustomer: {
name: '',
phone: ''
},
isQuickAdding: false,
quickAddError: '',

// Redeem Modal
showRedeemModal: false,
redeemError: '',

// Customer Search
async searchCustomers() {
if (this.customerSearch.length < 2) { this.customerResults=[]; return; } try { const response=await
    fetch(`/pos/customers/search?q=${encodeURIComponent(this.customerSearch)}`); const data=await response.json();
    this.customerResults=data; } catch (error) { console.error('Customer search error:', error);
    this.customerResults=[]; } }, selectCustomer(customer) { this.selectedCustomer=customer; this.customerSearch='' ;
    this.selectedCustomer=null; this.pointsToRedeem=0; this.pointsDiscount=0; }, // Quick Add Customer
    openQuickAddModal() { this.showQuickAddModal=true; this.newCustomer={ name: '' , phone: '' }; { if
    (this.$refs.quickAddNameInput) { this.$refs.quickAddNameInput.focus(); } }); }, closeQuickAddModal() {
    this.showQuickAddModal=false; this.newCustomer={ name: '' , phone: '' }; this.quickAddError='' ; }, async
    saveNewCustomer() { if (!this.newCustomer.name || !this.newCustomer.phone) {
    this.quickAddError='Nama dan No. HP wajib diisi' ; return; } this.isQuickAdding=true; this.quickAddError='' ; try {
    const response=await fetch('/pos/customers/quick-add', { method: 'POST' , headers: { 'Content-Type'
    : 'application/json' , 'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token" ]').content }, body:
    JSON.stringify(this.newCustomer) }); const data=await response.json(); if (!response.ok) {
    this.quickAddError=data.message || 'Gagal menambahkan pelanggan' ; return; } // Auto-select newly created customer
    this.selectedCustomer=data.customer; this.closeQuickAddModal(); } catch (error) {
    this.quickAddError='Terjadi kesalahan. Silakan coba lagi.' ; console.error(error); } finally {
    this.isQuickAdding=false; } }, // Redeem Points openRedeemModal() { this.showRedeemModal=true; this.redeemAmount=0;
    this.tempPointsDiscount=0; this.redeemError='' ; }, closeRedeemModal() { this.showRedeemModal=false;
    this.redeemAmount=0; this.tempPointsDiscount=0; this.redeemError='' ; }, if (!this.redeemAmount || this.redeemAmount
    <=0) { this.tempPointsDiscount=0; return; } // Rate: 100 points=Rp 10,000 (configurable via settings) // Simplified:
    1 point=Rp 100 this.tempPointsDiscount=this.redeemAmount * 100; }, confirmRedeemPoints() { if (!this.redeemAmount ||
    this.redeemAmount <=0) { this.redeemError='Masukkan jumlah poin' ; return; } if (this.redeemAmount>
    (this.selectedCustomer?.points_balance || 0)) {
    this.redeemError = 'Poin tidak mencukupi';
    return;
    }

    this.pointsToRedeem = this.redeemAmount;
    this.pointsDiscount = this.tempPointsDiscount;
    this.closeRedeemModal();
    },

    // Utility
    formatCurrency(value) {
    const num = parseInt(value) || 0;
    return 'Rp ' + new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
    }).format(num);
    },

    formatNumber(value) {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
    }
    }
    }