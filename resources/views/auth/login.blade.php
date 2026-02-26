<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - POS Retail</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans antialiased bg-gradient-to-br from-slate-900 via-blue-900 to-slate-800 min-h-screen flex items-center justify-center p-4 lg:p-8 overflow-hidden relative">

    <!-- Subtle Background Decor -->
    <div class="absolute top-20 right-20 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-20 left-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div
        class="w-full max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-24 relative z-10">

        <!-- Left Column: Branding / Marketing -->
        <div class="flex-1 text-white lg:pr-8 text-center lg:text-left hidden lg:block relative">
            <!-- Floating Elements -->
            <div
                class="absolute -top-12 left-10 w-20 h-20 border-2 border-slate-600/30 rounded-2xl transform rotate-12 pointer-events-none">
            </div>
            <div
                class="absolute top-32 right-12 w-14 h-14 border border-emerald-500/30 rounded-lg transform -rotate-12 pointer-events-none">
            </div>
            <div
                class="absolute bottom-16 left-32 w-24 h-24 border border-blue-400/20 rounded-full pointer-events-none">
            </div>
            <div
                class="absolute -bottom-8 right-24 w-16 h-16 border-2 border-slate-500/20 rounded-xl transform rotate-45 pointer-events-none">
            </div>
            <!-- Dashed circles (simulated via SVG) -->
            <svg class="absolute top-1/2 right-0 transform -translate-y-1/2 w-96 h-96 text-slate-700/20 pointer-events-none"
                fill="none" stroke="currentColor" stroke-dasharray="8 8" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" stroke-width="0.5" />
                <circle cx="50" cy="50" r="30" stroke-width="0.5" />
            </svg>

            <!-- Status Badge -->
            <div
                class="inline-flex items-center space-x-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-1.5 rounded-full mb-8 text-sm font-medium backdrop-blur-sm shadow-sm relative z-10">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Sistem Aktif</span>
            </div>

            <!-- Headline -->
            <h1 class="text-4xl lg:text-6xl font-extrabold mb-6 leading-tight relative z-10">
                Selamat<br>
                <span class="text-emerald-400">Datang Kembali</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-slate-300 text-lg mb-12 max-w-md mx-auto lg:mx-0 leading-relaxed font-light relative z-10">
                Kelola bisnis Anda dengan mudah melalui sistem POS yang cepat, aman, dan terpercaya.
            </p>

            <!-- Feature Badges -->
            <div class="flex flex-wrap gap-4 items-center justify-center lg:justify-start relative z-10">
                <div
                    class="flex items-center space-x-3 bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-3 shadow-md backdrop-blur-md hover:bg-slate-800/60 transition-colors">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                        </path>
                    </svg>
                    <span class="text-sm font-medium text-slate-200">Transaksi Aman</span>
                </div>

                <div
                    class="flex items-center space-x-3 bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-3 shadow-md backdrop-blur-md hover:bg-slate-800/60 transition-colors">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium text-slate-200">Real-time Data</span>
                </div>

                <div
                    class="flex items-center space-x-3 bg-slate-800/40 border border-slate-700/60 rounded-xl px-4 py-3 shadow-md backdrop-blur-md hover:bg-slate-800/60 transition-colors">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    <span class="text-sm font-medium text-slate-200">Multi User</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Login Form -->
        <div class="w-full max-w-md w-full relative z-10 flex flex-col justify-center">
            <!-- Logo Header (Mobile & Desktop) -->
            <div
                class="text-center lg:text-left mb-8 flex flex-col lg:flex-row items-center lg:items-center space-y-4 lg:space-y-0 lg:space-x-4">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 bg-emerald-500 rounded-2xl shadow-lg ring-4 ring-emerald-500/20">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">POSPro</h1>
                </div>
            </div>

            <div class="text-center lg:text-left mb-6">
                <h2 class="text-3xl font-bold text-white mb-2">Masuk ke Akun</h2>
                <p class="text-slate-400 text-sm">Masukkan kredensial Anda untuk melanjutkan</p>
            </div>

            <!-- The Form -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8 backdrop-blur-xl bg-opacity-90">
                @if($errors->any())
                    <div
                        class="mb-6 p-4 bg-red-900/40 border border-red-500/50 text-red-200 rounded-xl text-sm backdrop-blur-sm">
                        @foreach($errors->all() as $error)
                            <p class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $error }}
                            </p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Username /
                            Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-10 pr-4 py-3 bg-slate-800 border-slate-700 text-white placeholder-slate-500 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-inner"
                                placeholder="Masukkan username">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="w-full pl-10 pr-10 py-3 bg-slate-800 border-slate-700 text-white placeholder-slate-500 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-inner"
                                placeholder="Masukkan password">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="remember"
                                class="w-4 h-4 text-emerald-500 bg-slate-800 border-slate-600 rounded focus:ring-emerald-500 focus:ring-offset-slate-900 transition-colors">
                            <span class="text-sm text-slate-400 group-hover:text-slate-300 transition-colors">Ingat
                                saya</span>
                        </label>
                        <a href="#"
                            class="text-sm font-medium text-emerald-400 hover:text-emerald-300 transition-colors">Lupa
                            password?</a>
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-3.5 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 focus:ring-offset-slate-900 transition-all group">
                        Masuk
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>

                    <p class="text-center text-slate-500 text-xs mt-6">
                        Belum punya akun? <a href="#"
                            class="text-emerald-400 hover:text-emerald-300 transition-colors font-medium">Hubungi
                            administrator</a>
                    </p>
                </form>
            </div>

            <p class="text-center text-slate-500 text-xs mt-8">
                &copy; {{ date('Y') }} POSPro Retail. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>