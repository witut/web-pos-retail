<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PrinterService;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    protected PrinterService $printerService;

    public function __construct(PrinterService $printerService)
    {
        $this->printerService = $printerService;
    }

    /**
     * Get the JSON payload for the Print Server
     */
    public function getPayload(Transaction $transaction)
    {
        return response()->json([
            'success' => true,
            'data' => $this->printerService->generatePrintPayload($transaction)
        ]);
    }
}
