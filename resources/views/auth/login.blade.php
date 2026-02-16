<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Klinik Alisha</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind 4 via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="text-slate-600 antialiased h-screen overflow-hidden">

    <div class="flex h-full">
        {{-- Left Panel — Branding --}}
        <div class="hidden md:flex md:w-1/2 xl:w-[55%] relative overflow-hidden" style="background: linear-gradient(135deg, #075985 0%, #0c4a6e 50%, #082f49 100%)">
            {{-- Decorative Elements --}}
            <div class="absolute inset-0">
                <div class="absolute top-0 left-0 w-96 h-96 bg-white/8 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-white/8 rounded-full translate-x-1/4 translate-y-1/4"></div>
                <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-white/8 rounded-full"></div>
                {{-- Grid pattern --}}
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-between p-12 xl:p-16 w-full">
                {{-- Top: Logo --}}
                <div>
                    <div class="flex items-center gap-3">
                        <div class="bg-white/15 backdrop-blur-sm p-2.5 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </div>
                        <span class="text-white font-bold text-xl tracking-tight">Klinik Alisha</span>
                    </div>
                </div>

                {{-- Center: Hero Text --}}
                <div class="space-y-6">
                    <h2 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight">
                        Sistem Informasi<br>Manajemen Klinik
                    </h2>
                    <p class="text-primary-200 text-lg max-w-md leading-relaxed">
                        Kelola pendaftaran pasien, pemeriksaan, apotek, dan integrasi SatuSehat dalam satu platform terpadu.
                    </p>

                    {{-- Feature highlights --}}
                    <div class="grid grid-cols-2 gap-4 pt-4 max-w-md">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/10 rounded-lg p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <span class="text-white/90 text-sm font-medium">Pendaftaran</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-white/10 rounded-lg p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h.01"/><path d="M15 12h.01"/><path d="M10 16c.5.3 1.2.5 2 .5s1.5-.2 2-.5"/><path d="M19 6.3a9 9 0 0 1 1.8 3.9 2 2 0 0 1 0 3.6 9 9 0 0 1-17.6 0 2 2 0 0 1 0-3.6A9 9 0 0 1 12 3c2 0 3.5 1.1 3.5 2.5s-.9 2.5-2 2.5c-.8 0-1.5-.4-1.5-1"/></svg>
                            </div>
                            <span class="text-white/90 text-sm font-medium">Pemeriksaan</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-white/10 rounded-lg p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
                            </div>
                            <span class="text-white/90 text-sm font-medium">Apotek</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="bg-white/10 rounded-lg p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                            </div>
                            <span class="text-white/90 text-sm font-medium">SatuSehat</span>
                        </div>
                    </div>
                </div>

                {{-- Bottom: Location --}}
                <div class="flex items-center gap-2 text-primary-300 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Banda Aceh, Indonesia
                </div>
            </div>
        </div>

        {{-- Right Panel — Login Form --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-10 bg-slate-50">
            <div class="w-full max-w-md">
                {{-- Mobile-only brand header --}}
                <div class="md:hidden flex flex-col items-center mb-8">
                    <div class="bg-primary-50 p-3 rounded-xl border border-primary-100 text-primary-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Klinik Alisha</h1>
                    <p class="text-slate-500 text-sm mt-1">Banda Aceh</p>
                </div>

                {{-- Welcome text (desktop) --}}
                <div class="hidden md:block mb-8">
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang</h2>
                    <p class="text-slate-500 text-sm mt-1.5">Masuk ke akun Anda untuk melanjutkan</p>
                </div>

                {{-- Login Form --}}
                <x-ui.card class="p-6 sm:p-8">
                    <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                        @csrf

                        @if ($errors->any())
                        <div class="bg-red-200 border border-red-500 rounded-lg p-4 flex gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-700 shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                            <div class="text-sm text-red-800">
                                <p class="font-bold">Login Gagal</p>
                                <ul class="list-disc list-inside mt-1 space-y-0.5">
                                     @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif

                        <div class="space-y-1.5">
                            <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                            <x-ui.input type="email" name="email" id="email" required autofocus placeholder="nama@klinikalisha.com" value="{{ old('email') }}" />
                        </div>

                        <div class="space-y-1.5">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <x-ui.input type="password" name="password" id="password" required placeholder="••••••••" />
                        </div>

                        <div class="pt-1">
                            <x-ui.button type="submit" class="w-full justify-center py-2.5 text-base shadow-lg shadow-primary-500/20">
                                Masuk
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>

                {{-- Footer --}}
                <div class="mt-8 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Klinik Alisha &mdash; Banda Aceh
                    <div class="mt-2">
                        <a href="{{ route('antrian.display') }}" class="text-primary-600 hover:text-primary-700 font-medium hover:underline">
                            Lihat Display Antrian
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
