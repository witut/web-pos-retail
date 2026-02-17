{{-- Backup Settings Tab --}}
<div class="space-y-6">
    {{-- Automatic Backup --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">Backup Otomatis</h3>
                <p class="text-sm text-gray-600 mt-1">Aktifkan backup database otomatis setiap hari</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="backup_enabled" value="1" 
                       {{ ($settings['backup_enabled'] ?? false) ? 'checked' : '' }}
                       class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Backup</label>
                <input type="time" name="backup_time" 
                       value="{{ $settings['backup_time'] ?? '02:00' }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Backup akan dijalankan setiap hari pada waktu ini</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Retensi Backup (Hari)</label>
                <input type="number" name="backup_retention_days" 
                       value="{{ $settings['backup_retention_days'] ?? 30 }}"
                       min="1" max="365"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Backup lebih lama dari ini akan dihapus otomatis</p>
            </div>
        </div>
    </div>

    {{-- Email Notification (Optional) --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">Notifikasi Email</h3>
                <p class="text-sm text-gray-600 mt-1">Kirim email jika backup gagal (opsional, memerlukan konfigurasi SMTP)</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="backup_email_notification" value="1" 
                       {{ ($settings['backup_email_notification'] ?? false) ? 'checked' : '' }}
                       class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Penerima Notifikasi</label>
            <input type="email" name="backup_notification_email" 
                   value="{{ $settings['backup_notification_email'] ?? '' }}"
                   placeholder="admin@example.com"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin menerima email notifikasi</p>
        </div>

        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-yellow-900">Catatan Penting</h4>
                    <p class="text-sm text-yellow-700 mt-1">
                        Email notifikasi memerlukan konfigurasi SMTP di file .env. Jika SMTP tidak dikonfigurasi, 
                        notifikasi backup gagal hanya akan muncul di <strong>Notification Bell</strong> (pojok kanan atas).
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.backups.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                Kelola Backup
            </a>

            <button type="button" onclick="testBackup()" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Test Backup Sekarang
            </button>
        </div>
    </div>
</div>

<script>
function testBackup() {
    if (!confirm('Apakah Anda yakin ingin membuat backup sekarang untuk testing?')) {
        return;
    }

    // Redirect to backup page and trigger backup
    window.location.href = '{{ route("admin.backups.index") }}';
}
</script>
