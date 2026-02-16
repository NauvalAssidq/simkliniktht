<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SimKlinik THT' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind 4 via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="text-slate-600 antialiased h-screen flex overflow-hidden" x-data="{ sidebarOpen: true }">

    <x-layout.sidebar />

    <!-- Main Workspace -->
    <main class="flex-1 lg:ml-72 flex flex-col h-full bg-slate-50 relative transition-all duration-300">
        <x-layout.header :title="$title ?? 'SimKlinik'" />

        <!-- Content Area -->
        <div class="flex-1 relative flex flex-col min-h-0 overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                {{ $slot }}
            </div>
        </div>
        
        <x-ui.toast />
    </main>

</body>
</html>
