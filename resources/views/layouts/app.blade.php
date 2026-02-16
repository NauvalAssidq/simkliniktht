<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SimKlinik THT') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav x-data="{ open: false }" class="bg-white border-b border-slate-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="/" class="text-2xl font-bold text-primary tracking-tight">
                                SimKlinik <span class="text-slate-900">THT</span>
                            </a>
                        </div>
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('registration.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('registration.*') ? 'border-primary text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} text-sm font-medium transition duration-150 ease-in-out">
                                Pendaftaran
                            </a>
                            <a href="{{ route('pemeriksaan.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('pemeriksaan.*') ? 'border-primary text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} text-sm font-medium transition duration-150 ease-in-out">
                                Pemeriksaan
                            </a>
                            <a href="{{ route('antrian.display') }}" target="_blank" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300 transition duration-150 ease-in-out">
                                Antrian TV
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 focus:text-slate-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('registration.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('registration.*') ? 'border-primary text-primary bg-sky-50' : 'border-transparent text-slate-600 hover:text-slate-800 hover:bg-slate-50 hover:border-slate-300' }} text-base font-medium transition duration-150 ease-in-out">
                        Pendaftaran
                    </a>
                    <a href="{{ route('pemeriksaan.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('pemeriksaan.*') ? 'border-primary text-primary bg-sky-50' : 'border-transparent text-slate-600 hover:text-slate-800 hover:bg-slate-50 hover:border-slate-300' }} text-base font-medium transition duration-150 ease-in-out">
                        Pemeriksaan
                    </a>
                    <a href="{{ route('antrian.display') }}" target="_blank" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-slate-600 hover:text-slate-800 hover:bg-slate-50 hover:border-slate-300 text-base font-medium transition duration-150 ease-in-out">
                        Antrian TV
                    </a>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-1 py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

        <x-ui.toast />
        
        <footer class="bg-white border-t border-slate-200 mt-auto">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} SimKlinik THT. Integrated with SatuSehat.
                </p>
            </div>
        </footer>
    </div>
    

</body>
</html>
