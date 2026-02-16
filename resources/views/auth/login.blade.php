<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SimKlinik</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind 4 via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-600 antialiased h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo / Brand -->
        <div class="flex flex-col items-center mb-8">
            <div class="bg-primary-50 p-3 rounded-xl border border-primary-100 text-primary-600 mb-4 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">SimKlinik</h1>
            <p class="text-slate-500 text-sm mt-1">Sistem Informasi Manajemen Klinik</p>
        </div>

        <x-ui.card class="p-6 sm:p-8">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                @if ($errors->any())
                <div class="bg-red-50 border border-red-100 rounded-lg p-4 flex gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <div class="text-sm text-red-600">
                        <p class="font-bold">Login Gagal</p>
                        <ul class="list-disc list-inside mt-1">
                             @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <div class="space-y-2">
                    <label for="email" class="block text-sm font-bold text-slate-700">Email Address</label>
                    <x-ui.input type="email" name="email" id="email" required autofocus placeholder="nama@simklinik.com" value="{{ old('email') }}" />
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-sm font-bold text-slate-700">Password</label>
                    <x-ui.input type="password" name="password" id="password" required placeholder="••••••••" />
                </div>

                <div class="pt-2">
                    <x-ui.button type="submit" class="w-full justify-center py-2.5 text-base shadow-lg shadow-primary-500/20">
                        Sign In
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="mt-8 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} SimKlinik. All rights reserved.
            <div class="mt-2">
                <a href="{{ route('antrian.display') }}" class="text-primary-600 hover:text-primary-700 font-medium hover:underline">
                    Lihat Display Antrian
                </a>
            </div>
        </div>
    </div>

</body>
</html>
