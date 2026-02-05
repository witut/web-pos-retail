<x-layouts.admin :title="'Edit User'">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.users.index') }}" class="mr-4 p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit User</h1>
            <p class="text-gray-500 text-sm">{{ $user->name }} ({{ $user->email }})</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 @error('name') border-red-500 @enderror"
                        placeholder="Nama lengkap">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email <span
                            class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 @error('email') border-red-500 @enderror"
                        placeholder="email@contoh.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password Baru
                        <span class="text-gray-400 font-normal">(kosongkan jika tidak ingin mengubah)</span>
                    </label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 @error('password') border-red-500 @enderror"
                        placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                        placeholder="Ulangi password">
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role <span
                            class="text-red-500">*</span></label>
                    <select name="role" id="roleSelect" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 @error('role') border-red-500 @enderror {{ $user->id === auth()->id() ? 'bg-gray-100' : '' }}"
                        {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="cashier" {{ old('role', $user->role) == 'cashier' ? 'selected' : '' }}>Kasir
                        </option>
                    </select>
                    @if ($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <p class="mt-1 text-sm text-gray-500">Anda tidak dapat mengubah role sendiri</p>
                    @endif
                    @error('role')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status <span
                            class="text-red-500">*</span></label>
                    <select name="status" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 @error('status') border-red-500 @enderror {{ $user->id === auth()->id() ? 'bg-gray-100' : '' }}"
                        {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Aktif
                        </option>
                        <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Tidak
                            Aktif</option>
                    </select>
                    @if ($user->id === auth()->id())
                        <input type="hidden" name="status" value="{{ $user->status }}">
                        <p class="mt-1 text-sm text-gray-500">Anda tidak dapat mengubah status sendiri</p>
                    @endif
                    @error('status')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PIN (Admin only) -->
                <div id="pinField" class="md:col-span-2 {{ $user->role !== 'admin' ? 'hidden' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        PIN Baru
                        <span class="text-gray-400 font-normal">(kosongkan jika tidak ingin mengubah)</span>
                    </label>
                    <div class="flex items-center space-x-4">
                        <input type="password" name="pin" maxlength="6" pattern="\d{6}"
                            class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 @error('pin') border-red-500 @enderror"
                            placeholder="123456">
                        @if ($user->hasPin())
                            <span class="text-green-600 text-sm">✓ PIN sudah diset</span>
                        @else
                            <span class="text-gray-400 text-sm">PIN belum diset</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500">PIN digunakan untuk supervisor override (void transaksi, dll)
                    </p>
                    @error('pin')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.users.index') }}"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-colors">
                    Update User
                </button>
            </div>
        </form>
    </div>

    <!-- User Info Card -->
    <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Informasi User</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Dibuat</p>
                <p class="font-medium">{{ $user->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Terakhir Update</p>
                <p class="font-medium">{{ $user->updated_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total Transaksi</p>
                <p class="font-medium">{{ number_format($user->transactions()->count()) }}</p>
            </div>
            <div>
                <p class="text-gray-500">Penjualan Hari Ini</p>
                <p class="font-medium text-green-600">Rp {{ number_format($user->getTodaySales(), 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('roleSelect')?.addEventListener('change', function () {
                const pinField = document.getElementById('pinField');
                if (this.value === 'admin') {
                    pinField.classList.remove('hidden');
                } else {
                    pinField.classList.add('hidden');
                }
            });
        </script>
    @endpush
</x-layouts.admin>