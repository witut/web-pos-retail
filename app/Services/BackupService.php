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

            // Spatie backup:run tidak melempar exception saat gagal — deteksi dari output
            $failed = stripos($output, 'failed') !== false
                   || stripos($output, 'error') !== false
                   || stripos($output, 'exception') !== false;

            if ($failed) {
                // Log kegagalan
                AuditLog::create([
                    'user_id'      => auth()->id(),
                    'action_type'  => 'backup_failed',
                    'description'  => 'Manual backup failed (detected from output)',
                    'metadata'     => ['output' => substr($output, 0, 1000)]
                ]);

                $this->notificationService->createForAllAdmins(
                    'backup_failed',
                    'Backup Gagal',
                    'Backup manual gagal. Periksa konfigurasi mysqldump atau disk penyimpanan.',
                    ['output' => substr($output, 0, 500), 'timestamp' => now()]
                );

                return [
                    'success' => false,
                    'message' => 'Backup gagal dibuat. Kemungkinan penyebab: mysqldump tidak ditemukan atau disk tidak dapat diakses. Detail: ' . substr($output, 0, 300),
                    'output'  => $output
                ];
            }

            // Log sukses
            AuditLog::create([
                'user_id'     => auth()->id(),
                'action_type' => 'backup_created',
                'description' => 'Manual backup created successfully',
                'metadata'    => ['output' => substr($output, 0, 500)]
            ]);

            return [
                'success' => true,
                'message' => 'Backup berhasil dibuat',
                'output'  => $output
            ];

        } catch (Exception $e) {
            $this->notificationService->createForAllAdmins(
                'backup_failed',
                'Backup Gagal',
                'Backup manual gagal: ' . $e->getMessage(),
                ['error' => $e->getMessage(), 'timestamp' => now()]
            );

            AuditLog::create([
                'user_id'     => auth()->id(),
                'action_type' => 'backup_failed',
                'description' => 'Manual backup failed',
                'metadata'    => ['error' => $e->getMessage()]
            ]);

            return [
                'success' => false,
                'message' => 'Backup gagal: ' . $e->getMessage(),
                'output'  => null
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

        // Coba baca file; jika folder tidak ada, kembalikan koleksi kosong
        try {
            $files = $backupDisk->exists($backupPath)
                ? $backupDisk->files($backupPath)
                : [];
        } catch (Exception $e) {
            $files = [];
        }

        return collect($files)->map(function ($file) use ($backupDisk) {
            return [
                'filename'       => basename($file),
                'path'           => $file,
                'size'           => $backupDisk->size($file),
                'size_formatted' => $this->formatBytes($backupDisk->size($file)),
                'date'           => $backupDisk->lastModified($file),
                'date_formatted' => \Carbon\Carbon::createFromTimestamp($backupDisk->lastModified($file))->format('d M Y, H:i'),
            ];
        })->sortByDesc('date')->values();
    }

    /**
     * Restore database from a backup file
     */
    public function restoreBackup(string $filename): array
    {
        $backupDisk = Storage::disk(config('backup.backup.destination.disks')[0]);
        $backupPath = config('backup.backup.name') . '/' . $filename;

        if (!$backupDisk->exists($backupPath)) {
            return ['success' => false, 'message' => 'File backup tidak ditemukan.'];
        }

        $localPath = $backupDisk->path($backupPath);
        $tempDir   = storage_path('app/backup-restore-temp-' . uniqid());

        try {
            // Ekstrak zip
            if (!extension_loaded('zip')) {
                return ['success' => false, 'message' => 'PHP ext-zip tidak tersedia.'];
            }

            File::makeDirectory($tempDir, 0755, true);

            $zip = new \ZipArchive();
            if ($zip->open($localPath) !== true) {
                return ['success' => false, 'message' => 'Gagal membuka file backup zip.'];
            }
            $zip->extractTo($tempDir);
            $zip->close();

            // Cari file .sql di dalam zip (mungkin di subfolder)
            $sqlFiles = File::allFiles($tempDir);
            $sqlFile  = null;
            foreach ($sqlFiles as $file) {
                if (strtolower($file->getExtension()) === 'sql') {
                    $sqlFile = $file->getRealPath();
                    break;
                }
            }

            if (!$sqlFile) {
                File::deleteDirectory($tempDir);
                return ['success' => false, 'message' => 'File SQL tidak ditemukan di dalam backup.'];
            }

            // Jalankan restore via mysql CLI
            $dbHost     = config('database.connections.mysql.host', '127.0.0.1');
            $dbPort     = config('database.connections.mysql.port', '3306');
            $dbName     = config('database.connections.mysql.database');
            $dbUser     = config('database.connections.mysql.username');
            $dbPassword = config('database.connections.mysql.password');
            $dumpPath   = config('database.connections.mysql.dump.dump_binary_path', '');

            // Tentukan binary mysql (bukan mysqldump)
            $mysqlBin = rtrim($dumpPath, '/\\');
            $mysqlBin = $mysqlBin
                ? (DIRECTORY_SEPARATOR === '\\' ? $mysqlBin . '\\mysql.exe' : $mysqlBin . '/mysql')
                : 'mysql';

            // Build command
            $passwordArg = $dbPassword ? '-p' . escapeshellarg($dbPassword) : '';
            $cmd = sprintf(
                '%s -h %s -P %s -u %s %s %s < %s 2>&1',
                escapeshellcmd($mysqlBin),
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                $passwordArg,
                escapeshellarg($dbName),
                escapeshellarg($sqlFile)
            );

            exec($cmd, $output, $returnCode);

            File::deleteDirectory($tempDir);

            if ($returnCode !== 0) {
                return [
                    'success' => false,
                    'message' => 'Restore gagal. Output: ' . implode(' ', $output),
                ];
            }

            // Audit log
            AuditLog::create([
                'user_id'     => auth()->id(),
                'action_type' => 'backup_restored',
                'description' => "Database restored from backup: {$filename}",
                'metadata'    => ['filename' => $filename],
            ]);

            return ['success' => true, 'message' => 'Database berhasil dipulihkan dari backup ' . $filename];

        } catch (Exception $e) {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            return ['success' => false, 'message' => 'Restore gagal: ' . $e->getMessage()];
        }
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
