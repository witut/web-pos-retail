<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * UserController
 * 
 * Controller untuk manajemen user (Admin dan Cashier)
 */
class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->role) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->withCount('transactions')
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,cashier',
            'pin' => 'nullable|digits:6',
            'status' => 'required|in:active,inactive',
        ]);

        // Create user (password akan di-hash otomatis via cast)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'pin' => $validated['pin'] ?? null, // PIN juga akan di-hash via cast
            'status' => $validated['status'],
        ]);

        \App\Models\AuditLog::logAction(
            'USER_CREATED',
            'users',
            (string) $user->id,
            null,
            ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin,cashier',
            'pin' => 'nullable|digits:6',
            'status' => 'required|in:active,inactive',
        ]);

        // Prevent user from changing their own role/status (avoid admin lockout)
        if ($user->id === auth()->id()) {
            unset($validated['role'], $validated['status']);
        }

        // Build update data
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'] ?? $user->role,
            'status' => $validated['status'] ?? $user->status,
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        // Only update PIN if provided
        if (!empty($validated['pin'])) {
            $updateData['pin'] = $validated['pin'];
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent user from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak bisa menghapus akun sendiri']);
        }

        // Check if user has transactions
        if ($user->transactions()->count() > 0) {
            return back()->withErrors(['error' => 'User tidak bisa dihapus karena memiliki riwayat transaksi']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus');
    }

    /**
     * Reset PIN for admin user
     */
    public function resetPin(Request $request, User $user)
    {
        $validated = $request->validate([
            'pin' => 'required|digits:6',
        ]);

        // Only admin users can have PIN
        if (!$user->isAdmin()) {
            return back()->withErrors(['error' => 'Hanya admin yang bisa memiliki PIN']);
        }

        $user->update(['pin' => $validated['pin']]);

        \App\Models\AuditLog::logAction(
            'ADMIN_ACTION',
            'users',
            (string) $user->id,
            null,
            ['action' => 'reset_pin', 'target_user' => $user->name]
        );

        return back()->with('success', 'PIN berhasil direset');
    }

    /**
     * Remove PIN from user
     */
    public function removePin(User $user)
    {
        $user->update(['pin' => null]);

        return back()->with('success', 'PIN berhasil dihapus');
    }
}
