{{-- Customer Selected Details Bar (shown below search bar when a customer is active) --}}
<div x-data="customerComponent()" @restore-customer-state.window="selectCustomer($event.detail)"
    @clear-customer.window="clearCustomer()" @restore-full-state.window="restoreState($event.detail)"
    @open-redeem-with-cart.window="initRedeemModal($event.detail)" x-show="selectedCustomer" x-cloak>
    <div class="bg-white rounded-xl shadow-sm px-4 py-2.5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                {{-- Customer Info --}}
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-medium text-gray-800" x-text="selectedCustomer?.name"></span>
                    <span class="text-sm text-gray-500" x-text="selectedCustomer?.phone"></span>
                </div>

                {{-- Points Badge --}}
                <span
                    class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span x-text="formatNumber(selectedCustomer?.points_balance || 0)"></span> poin
                </span>

                {{-- Tukar Poin Button --}}
                <button @click="$dispatch('request-cart-total-for-redeem')"
                    x-show="selectedCustomer?.points_balance > 0"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50 transition-colors">
                    Tukar Poin
                </button>
            </div>

            {{-- Redeemed Points display --}}
            <div x-show="pointsToRedeem > 0" class="flex items-center gap-3 text-sm">
                <span class="text-gray-500">Ditukar: <span class="font-medium text-orange-600"
                        x-text="formatNumber(pointsToRedeem)"></span> poin</span>
                <span class="text-gray-300">|</span>
                <span class="font-bold text-green-600" x-text="'-' + formatCurrency(pointsDiscount)"></span>
            </div>
        </div>
    </div>

    {{-- Redeem Points Modal --}}
    @include('cashier.pos._customer-redeem-modal')
</div>