@props(['title'])

<header class="h-16 bg-white border-b border-neutral-200 sticky top-0 z-30 flex items-center justify-between px-6">
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-neutral-100 text-slate-500 lg:hidden">
            <!-- Menu icon (SVG) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </button>
        <h2 class="text-lg font-bold text-slate-800">{{ $title }}</h2>
    </div>
    
    <div class="flex items-center gap-4">
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-primary-50 rounded-full border border-primary-100 text-primary-700 text-xs font-bold">
            <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
            ONLINE
        </div>
    </div>
</header>
