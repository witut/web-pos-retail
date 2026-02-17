<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\StreamedResponse;
use App\Services\NotificationService;
use App\Models\AuditLog;
use Exception;

class BackupService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new backup
     */
    public function createBackup(): array
    {
        try {
            Artisan::call('backup:run', ['--only-db' => true]);

            $output = Artisan::output();

            // Log to audit
            AuditLog::create([
                'user_id' => auth()->id(),
                'action_type' => 'backup_created',
                'description' => 'Manual backup created successfully',
                'metadata' => ['output' => substr($output, 0, 500)]
            ]);

            return [
                'success' => true,
                'message' => 'Backup berhasil dibuat',
                'output' => $output
            ];
        } catch (Exception $e) {
            // Create notification for admins
            $this->notificationService->createForAllAdmins(
                'backup_failed',
                'Backup Gagal',
                'Backup manual gagal: ' . $e->getMessage(),
                ['error' => $e->getMessage(), 'timestamp' => now()]
            );

            // Log error to audit
            AuditLog::create([
                'user_id' => auth()->id(),
                'action_type' => 'backup_failed',
                'description' => 'Manual backup failed',
                'metadata' => ['error' => $e->getMessage()]
            ]);

            return [
                'success' => false,
                'message' => 'Backup gagal: ' . $e->getMessage(),
                'output' => null
            ];
        }
    }

    /**
     * List all backup files
     */
    public function listBackups(): Collection
    {
        $backupDisk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $backupPath = config('backup.backup.name');
        $files = $backupDisk->files($backupPath);

        return collect($files)->map(function ($file) use ($backupDisk) {
            return [
                'filename' => basename($file),
                'path' => $file,
                'size' => $backupDisk->size($file),
                'size_formatted' => $this->formatBytes($backupDisk->size($file)),
                'date' => $backupDisk->lastModified($file),
                'date_formatted' => \Carbon\Carbon::createFromTimestamp($backupDisk->lastModified($file))->format('d M Y, H:i'),
            ];
        })->sortByDesc('date')->values();
    }

    /**
     * Download a backup file
     */
    public function downloadBackup(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $backupDisk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $backupPath = config('backup.backup.name') . '/' . $filename;

        if (!$backupDisk->exists($backupPath)) {
            abort(404, 'Backup file not found');
        }

        // Log to audit
        AuditLog::create([
            'user_id' => auth()->id(),
            'action_type' => 'backup_downloaded',
            'description' => "Backup file downloaded: {$filename}",
            'metadata' => ['filename' => $filename]
        ]);


        return response()->download($backupDisk->path($backupPath), $filename);
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(string $filename): bool
    {
        $backupDisk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $backupPath = config('backup.backup.name') . '/' . $filename;

        if (!$backupDisk->exists($backupPath)) {
            return false;
        }

        $deleted = $backupDisk->delete($backupPath);

        if ($deleted) {
            // Log to audit
            AuditLog::create([
                'user_id' => auth()->id(),
                'action_type' => 'backup_deleted',
                'description' => "Backup file deleted: {$filename}",
                'metadata' => ['filename' => $filename]
            ]);
        }

        return $deleted;
    }

    /**
     * Clean old backups based on retention days
     */
    public function cleanOldBackups(int $days): int
    {
        Artisan::call('backup:clean');

        return 0; // Spatie backup:clean handles this
    }

    /**
     * Format bytes to human readable size
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
