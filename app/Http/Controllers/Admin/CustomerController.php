<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    protected $customerService;
    protected $settingService;

    public function __construct(CustomerService $customerService, SettingService $settingService)
    {
        $this->customerService = $customerService;
        $this->settingService = $settingService;
    }

    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::with('transactions');

        // Search
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $customers = $query->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully');
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        $customer->load([
            'transactions' => function ($query) {
                $query->where('status', 'completed')->orderBy('transaction_date', 'desc')->limit(20);
            }
        ]);

        $pointsHistory = $this->customerService->getPointsHistory($customer)->take(20);

        return view('admin.customers.show', compact('customer', 'pointsHistory'));
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully');
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    /**
     * Search customers (API for POS)
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');

        $customers = Customer::search($search)
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email', 'points_balance']);

        return response()->json($customers);
    }

    /**
     * Quick store customer from POS (Nama + HP only)
     */
    public function quickStore(Request $request)
    {
        // Check if cashier can create customers
        if (!$this->settingService->getBool('customer.cashier_can_create', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menambahkan pelanggan baru'
            ], 403);
        }

        // Check daily limit if enabled
        if ($this->settingService->getBool('customer.cashier_limit_enabled', false)) {
            $limit = $this->settingService->getInt('customer.cashier_daily_limit', 20);
            $cashierId = auth()->id();

            $todayCount = Customer::whereDate('created_at', today())
                ->where('created_by', $cashierId)
                ->count();

            if ($todayCount >= $limit) {
                return response()->json([
                    'success' => false,
                    'message' => "Batas harian tercapai. Anda sudah menambahkan {$limit} pelanggan hari ini."
                ], 429);
            }
        }

        // Validate minimal fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
        ]);

        // Track who created this customer
        $validated['created_by'] = auth()->id();

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil ditambahkan',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'points_balance' => $customer->points_balance,
            ]
        ]);
    }
}
