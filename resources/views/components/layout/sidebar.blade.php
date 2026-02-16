<aside class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transition-transform duration-300 transform lg:translate-x-0 flex flex-col" :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
    <!-- Brand -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200">
        <div class="flex items-center gap-3 text-primary-600">
            <div class="bg-primary-50 p-1.5 rounded-lg border border-primary-100">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity w-5 h-5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div>
                <h1 class="font-bold text-lg text-slate-800 leading-none">SimKlinik</h1>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-2">
        <div class="px-3 mb-4 text-xs font-bold text-slate-400">Menu Utama</div>

        <a href="#" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
            Beranda
        </a>

        @if(in_array(auth()->user()->role, ['pendaftaran']))
        <a href="{{ route('registration.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('registration.*') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Pendaftaran
        </a>
        @endif

        @if(auth()->user()->role === 'dokter')
        <a href="{{ route('pemeriksaan.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('pemeriksaan.*') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/><path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/><circle cx="20" cy="10" r="2"/></svg>
            Pemeriksaan Dokter
        </a>
        @endif

        @if(auth()->user()->role === 'apotek')
        <a href="{{ route('apotek.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('apotek.*') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pill w-4 h-4"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
            Farmasi / Apotek
        </a>
        @endif

        @if(auth()->user()->role !== 'admin')
        <div class="px-3 mt-8 mb-4 text-xs font-medium text-slate-400">Antrian</div>

        <a href="{{ route('antrian.display') }}" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('antrian.display') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="m10 7 5 3-5 3Z"/><rect width="20" height="14" x="2" y="3" rx="2"/><path d="M12 17v4"/><path d="M8 21h8"/></svg>
            Display Antrian TV
        </a>
        @endif
        @if(auth()->user()->role === 'admin')
        <div class="px-3 mt-8 mb-4 text-xs font-medium text-slate-400">SatuSehat</div>

        <a href="{{ route('satusehat.practitioner') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('satusehat.practitioner*') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-stethoscope w-4 h-4"><path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/><path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/><circle cx="20" cy="10" r="2"/></svg>
            Manajemen Praktisi
        </a>

        <a href="{{ route('staff.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('staff.*') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            Manajemen Staff
        </a>

        <a href="{{ route('satusehat.icd10') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('satusehat.icd10*') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            ICD-10
        </a>

        <a href="{{ route('satusehat.icd9') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('satusehat.icd9*') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
            ICD-9
        </a>

        <a href="{{ route('satusehat.kfa') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group {{ request()->routeIs('satusehat.kfa*') ? 'bg-primary-50 text-primary-600 border border-primary-600' : 'text-slate-500 hover:bg-slate-50 hover:text-primary-600' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
            Data Obat / KFA
        </a>
        @endif
    </nav>

    <!-- User Dropdown Component -->
    <x-layout.user-dropdown />
</aside>
