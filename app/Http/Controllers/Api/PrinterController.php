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

    /**
     * Proxy the print request to the Print Server to avoid CORS
     */
    public function printProxy(Transaction $transaction)
    {
        try {
            $payload = $this->printerService->generatePrintPayload($transaction);
            $serverUrl = \App\Models\Setting::get('printer.server_url', 'http://localhost:9100');

            // Ensure URL doesn't end with slash, then append /print
            $serverUrl = rtrim($serverUrl, '/');
            $printEndpoint = $serverUrl . '/print';

            $response = \Illuminate\Support\Facades\Http::timeout(5)->post($printEndpoint, $payload);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Struk berhasil dikirim ke print server'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Print Server merespon dengan error: ' . $response->status()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghubungi Print Server: ' . $e->getMessage()
            ], 500);
        }
    }
}
