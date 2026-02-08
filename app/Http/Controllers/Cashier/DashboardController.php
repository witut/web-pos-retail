<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class DashboardController extends Controller
{
    /**
     * Display cashier dashboard with personal stats
     */
    public function index()
    {
        $user = auth()->user();

        // Get today's stats for this cashier
        $todaySales = $user->getTodaySales();
        $todayTransactionCount = $user->getTodayTransactionCount();

        // Get recent transactions (limit 5)
        $recentTransactions = $user->transactions()
            ->latest()
            ->limit(5)
            ->get();

        return view('cashier.dashboard', compact('todaySales', 'todayTransactionCount', 'recentTransactions'));
    }

    /**
     * Show profile edit form
     */
    public function profile()
    {
        return view('cashier.profile', ['user' => auth()->user()]);
    }

    /**
     * Update profile (name, password)
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        // Update name
        $user->name = $validated['name'];

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui');
    }
}
