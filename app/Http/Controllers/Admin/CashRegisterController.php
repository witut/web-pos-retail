<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $registers = CashRegister::orderBy('name')->paginate(10);
        return view('admin.cash-registers.index', compact('registers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cash-registers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:cash_registers,name',
            'status' => 'required|in:active,inactive',
            'ip_address' => 'nullable|string|ip'
        ]);

        CashRegister::create($request->all());

        return redirect()->route('admin.cash-registers.index')
            ->with('success', 'Mesin Kasir berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashRegister $cashRegister)
    {
        return view('admin.cash-registers.edit', compact('cashRegister'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:cash_registers,name,' . $cashRegister->id,
            'status' => 'required|in:active,inactive',
            'ip_address' => 'nullable|string|ip'
        ]);

        $cashRegister->update($request->all());

        return redirect()->route('admin.cash-registers.index')
            ->with('success', 'Mesin Kasir berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashRegister $cashRegister)
    {
        // Don't delete if it has sessions
        if ($cashRegister->sessions()->count() > 0) {
            return back()->with('error', 'Mesin kasir tidak dapat dihapus karena memiliki riwayat sesi transaksi. Silakan ubah statusnya menjadi Inaktif.');
        }

        $cashRegister->delete();

        return redirect()->route('admin.cash-registers.index')
            ->with('success', 'Mesin Kasir berhasil dihapus.');
    }
}
