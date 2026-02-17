<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\NotificationService;
use App\Services\SettingService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily Backup (if enabled in settings)
Schedule::call(function () {
    $settingService = app(SettingService::class);
    $notificationService = app(NotificationService::class);

    $backupEnabled = $settingService->get('backup_enabled', false);

    if (!$backupEnabled) {
        return;
    }

    try {
        Artisan::call('backup:run', ['--only-db' => true]);

        \App\Models\AuditLog::create([
            'user_id' => null,
            'action_type' => 'backup_created',
            'description' => 'Automatic daily backup completed successfully',
            'metadata' => ['scheduled' => true]
        ]);
    } catch (\Exception $e) {
        // Create notification for all admins
        $notificationService->createForAllAdmins(
            'backup_failed',
            'Backup Otomatis Gagal',
            'Backup harian gagal dijalankan: ' . $e->getMessage(),
            ['error' => $e->getMessage(), 'timestamp' => now(), 'scheduled' => true]
        );

        \App\Models\AuditLog::create([
            'user_id' => null,
            'action_type' => 'backup_failed',
            'description' => 'Automatic daily backup failed',
            'metadata' => ['error' => $e->getMessage(), 'scheduled' => true]
        ]);

        // Send email if enabled
        $emailEnabled = $settingService->get('backup_email_notification', false);
        if ($emailEnabled) {
            $email = $settingService->get('backup_notification_email');
            if ($email) {
                try {
                    \Mail::raw(
                        "Backup otomatis gagal pada " . now()->format('d M Y H:i') . "\n\nError: " . $e->getMessage(),
                        function ($message) use ($email) {
                            $message->to($email)
                                ->subject('Backup Otomatis Gagal - POS Retail');
                        }
                    );
                } catch (\Exception $mailException) {
                    // Email failed, but notification already created
                    \Log::error('Failed to send backup failure email: ' . $mailException->getMessage());
                }
            }
        }
    }
})->dailyAt('02:00')->name('daily-backup'); // Default time 02:00 AM

// Clean old backups based on retention settings
Schedule::call(function () {
    $settingService = app(SettingService::class);
    $backupEnabled = $settingService->get('backup_enabled', false);

    if (!$backupEnabled) {
        return;
    }

    Artisan::call('backup:clean');
})->daily()->at('03:00')->name('clean-old-backups');

// Clean old notifications (older than 30 days)
Schedule::call(function () {
    $notificationService = app(NotificationService::class);
    $notificationService->deleteOld(30);
})->daily()->at('04:00')->name('clean-old-notifications');
