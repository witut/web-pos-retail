<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegisterSession;
use App\Services\ShiftService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    protected $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sessions = CashRegisterSession::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.shift.index', compact('sessions'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $session = CashRegisterSession::with('user', 'cashMovements')->findOrFail($id);
        $report = $this->shiftService->getReport($session);

        return view('admin.shift.show', compact('session', 'report'));
    }
}
