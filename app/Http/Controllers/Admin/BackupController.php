<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display a listing of backups
     */
    public function index(): View
    {
        $backups = $this->backupService->listBackups();

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Create a new backup
     */
    public function store(): RedirectResponse
    {
        $result = $this->backupService->createBackup();

        if ($result['success']) {
            return redirect()->route('admin.backups.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.backups.index')
            ->with('error', $result['message']);
    }

    /**
     * Download a backup file
     */
    public function download(string $filename)
    {
        return $this->backupService->downloadBackup($filename);
    }

    /**
     * Delete a backup file
     */
    public function destroy(string $filename): RedirectResponse
    {
        $deleted = $this->backupService->deleteBackup($filename);

        if ($deleted) {
            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup berhasil dihapus');
        }

        return redirect()->route('admin.backups.index')
            ->with('error', 'Backup tidak ditemukan');
    }
}
