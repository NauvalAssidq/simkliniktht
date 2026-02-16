<x-layout.app>
    <x-slot:title>Dashboard Farmasi</x-slot:title>

    <div class="p-6 lg:p-8" x-data="pharmacyDashboard()">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-amber-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-amber-800">{{ $stats['menunggu'] }}</p>
                    <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Menunggu</p>
                </div>
            </div>
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-emerald-800">{{ $stats['diberikan'] }}</p>
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Diserahkan Hari Ini</p>
                </div>
            </div>
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-800">{{ $stats['total'] }}</p>
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Total Hari Ini</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- LEFT: Prescription List --}}
            <div class="lg:col-span-2">
                <x-ui.card class="overflow-hidden">
                    <div class="p-4 border-b border-neutral-200 bg-neutral-50">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-white rounded-lg border border-neutral-200 text-primary-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">Daftar Resep</h2>
                                <p class="text-xs text-slate-500">Resep masuk dari dokter</p>
                            </div>
                        </div>

                        {{-- Status Tabs --}}
                        <div class="flex gap-1 bg-white p-1 rounded-lg border border-neutral-200">
                            <a href="{{ route('apotek.index', ['status' => 'menunggu']) }}"
                               class="flex-1 text-center px-3 py-1.5 rounded-md text-xs font-bold transition-all {{ $status === 'menunggu' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                                Menunggu
                                @if($stats['menunggu'] > 0)
                                <span class="ml-1 {{ $status === 'menunggu' ? 'bg-amber-400' : 'bg-amber-100 text-amber-700' }} px-1.5 py-0.5 rounded-full text-[10px] font-black">{{ $stats['menunggu'] }}</span>
                                @endif
                            </a>
                            <a href="{{ route('apotek.index', ['status' => 'diberikan']) }}"
                               class="flex-1 text-center px-3 py-1.5 rounded-md text-xs font-bold transition-all {{ $status === 'diberikan' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                                Selesai
                            </a>
                            <a href="{{ route('apotek.index', ['status' => 'semua']) }}"
                               class="flex-1 text-center px-3 py-1.5 rounded-md text-xs font-bold transition-all {{ $status === 'semua' ? 'bg-slate-500 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                                Semua
                            </a>
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="px-4 py-2 border-b border-neutral-100">
                        <form method="GET" action="{{ route('apotek.index') }}" class="flex items-center gap-2" onsubmit="if(!this.search.value.trim()){this.search.disabled=true;}">
                            <input type="hidden" name="status" value="{{ $status }}" />
                            <div class="relative flex-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pasien / no resep..."
                                    class="w-full rounded-lg pl-8 pr-3 py-2 border border-neutral-200 text-xs focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none" />
                            </div>
                            <button type="submit" class="px-3 py-2 bg-primary-600 text-white rounded-lg text-xs font-bold hover:bg-primary-700 transition-colors">Cari</button>
                        </form>
                    </div>

                    {{-- Prescription List --}}
                    <div class="divide-y divide-neutral-100 max-h-[calc(100vh-420px)] overflow-y-auto">
                        @forelse($prescriptions as $rx)
                        <div class="px-4 py-3 hover:bg-primary-50/50 cursor-pointer transition-all border-l-4 {{ $status === 'menunggu' ? 'border-l-amber-400' : ($rx->status === 'diberikan' ? 'border-l-emerald-400' : 'border-l-rose-300') }}"
                             :class="{ 'bg-primary-50 ring-1 ring-inset ring-primary-200': activeResep === '{{ $rx->no_resep }}' }"
                             @click="loadDetail('{{ $rx->no_resep }}')">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-mono font-black text-xs text-primary-700">{{ $rx->no_resep }}</span>
                                @if($rx->status === 'menunggu')
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px] font-bold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                        Menunggu
                                    </span>
                                @elseif($rx->status === 'diberikan')
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-bold">✓ Selesai</span>
                                @else
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded-full text-[10px] font-bold">✕ Batal</span>
                                @endif
                            </div>
                            <p class="text-sm font-bold text-slate-800 truncate">{{ $rx->regPeriksa->pasien->nm_pasien ?? '-' }}</p>
                            <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-400">
                                <span>Dr. {{ $rx->dokter->nm_dokter ?? '-' }}</span>
                                <span>•</span>
                                <span>{{ $rx->tgl_resep }} {{ substr($rx->jam_resep, 0, 5) }}</span>
                                <span>•</span>
                                <span>{{ $rx->detail->count() }} obat</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-slate-300 w-8 h-8"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
                            <p class="text-sm">Tidak ada resep ditemukan.</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    @if($prescriptions->hasPages())
                    <div class="px-4 py-3 border-t border-neutral-200 bg-neutral-50">
                        {{ $prescriptions->links() }}
                    </div>
                    @endif
                </x-ui.card>
            </div>

            {{-- RIGHT: Detail Panel --}}
            <div class="lg:col-span-3">
                <x-ui.card class="overflow-hidden sticky top-24">
                    {{-- Empty state --}}
                    <div x-show="!activeResep" class="p-12 text-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-slate-200 w-12 h-12"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
                        <p class="font-bold text-lg text-slate-300">Pilih Resep</p>
                        <p class="text-sm mt-1">Klik resep di sebelah kiri untuk melihat detail.</p>
                    </div>

                    {{-- Loading --}}
                    <div x-show="loading" class="p-12 text-center">
                        <svg class="animate-spin h-8 w-8 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-slate-400 mt-2">Memuat detail...</p>
                    </div>

                    {{-- Detail --}}
                    <div x-show="activeResep && !loading" x-cloak>
                        {{-- Header --}}
                        <div class="px-6 py-4 border-b border-neutral-200 bg-neutral-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-black text-slate-800" x-text="'Resep ' + detail.no_resep"></h3>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        <span x-text="detail.tgl_resep"></span> <span x-text="detail.jam_resep?.substring(0,5)"></span>
                                    </p>
                                </div>
                                <template x-if="detail.status === 'menunggu'">
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold flex items-center gap-1.5">
                                        <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                                        Menunggu Penyerahan
                                    </span>
                                </template>
                                <template x-if="detail.status === 'diberikan'">
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">✓ Sudah Diserahkan</span>
                                </template>
                                <template x-if="detail.status === 'batal'">
                                    <span class="px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-bold">✕ Dibatalkan</span>
                                </template>
                            </div>
                        </div>

                        {{-- Patient & Doctor Info --}}
                        <div class="px-6 py-3 border-b border-neutral-100 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pasien</p>
                                <p class="font-bold text-slate-800 mt-0.5" x-text="detail.pasien"></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dokter</p>
                                <p class="font-bold text-slate-800 mt-0.5" x-text="'Dr. ' + detail.dokter"></p>
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-neutral-50 text-slate-500 text-xs border-b border-neutral-200">
                                    <tr>
                                        <th class="px-6 py-2.5 text-left font-bold uppercase tracking-wider">#</th>
                                        <th class="px-6 py-2.5 text-left font-bold uppercase tracking-wider">Nama Obat</th>
                                        <th class="px-6 py-2.5 text-center font-bold uppercase tracking-wider">Jumlah</th>
                                        <th class="px-6 py-2.5 text-left font-bold uppercase tracking-wider">Dosis</th>
                                        <th class="px-6 py-2.5 text-left font-bold uppercase tracking-wider">Frekuensi</th>
                                        <th class="px-6 py-2.5 text-left font-bold uppercase tracking-wider">Instruksi</th>
                                        <th class="px-6 py-2.5 text-right font-bold uppercase tracking-wider">Stok</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    <template x-for="(item, idx) in detail.items" :key="idx">
                                        <tr class="hover:bg-neutral-50 transition-colors">
                                            <td class="px-6 py-2.5 text-xs text-slate-400" x-text="idx + 1"></td>
                                            <td class="px-6 py-2.5">
                                                <span class="font-bold text-slate-800" x-text="item.nama_obat"></span>
                                                <span class="text-[10px] text-slate-400 block" x-text="item.kd_brng"></span>
                                            </td>
                                            <td class="px-6 py-2.5 text-center">
                                                <span class="font-bold" x-text="item.jumlah"></span>
                                                <span class="text-xs text-slate-400" x-text="item.satuan"></span>
                                            </td>
                                            <td class="px-6 py-2.5 text-slate-600 text-xs" x-text="item.dosis || '-'"></td>
                                            <td class="px-6 py-2.5 text-slate-600 text-xs" x-text="item.frekuensi || '-'"></td>
                                            <td class="px-6 py-2.5 text-slate-600 text-xs" x-text="item.instruksi || '-'"></td>
                                            <td class="px-6 py-2.5 text-right">
                                                <span class="text-xs font-bold" :class="item.stok >= item.jumlah ? 'text-emerald-600' : 'text-rose-500'" x-text="item.stok"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        {{-- Action Buttons --}}
                        <template x-if="detail.status === 'menunggu'">
                            <div class="px-6 py-4 border-t border-neutral-200 bg-neutral-50 flex items-center justify-between">
                                <button @click="cancelResep()" class="px-4 py-2 border border-rose-200 text-rose-600 rounded-lg text-sm font-bold hover:bg-rose-50 transition-colors flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                    Batalkan Resep
                                </button>
                                <button @click="dispenseResep()" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-200 flex items-center gap-2" :disabled="dispensing">
                                    <template x-if="!dispensing">
                                        <span class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                            Serahkan Obat
                                        </span>
                                    </template>
                                    <template x-if="dispensing">
                                        <span class="flex items-center gap-2">
                                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            Memproses...
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </template>
                    </div>
                </x-ui.card>
            </div>
        </div>

    </div>

    <script>
        function pharmacyDashboard() {
            return {
                activeResep: null,
                detail: {},
                loading: false,
                dispensing: false,

                loadDetail(noResep) {
                    this.activeResep = noResep;
                    this.loading = true;
                    fetch(`/apotek/resep/${noResep}`, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.detail = data;
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
                },

                dispenseResep() {
                    if (!confirm('Konfirmasi: Serahkan semua obat dalam resep ini?')) return;
                    this.dispensing = true;
                    fetch(`/apotek/resep/${this.activeResep}/dispense`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.dispensing = false;
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'success' } }));
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'error' } }));
                        }
                    })
                    .catch(() => {
                        this.dispensing = false;
                    });
                },

                cancelResep() {
                    if (!confirm('Yakin ingin membatalkan resep ini?')) return;
                    fetch(`/apotek/resep/${this.activeResep}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'success' } }));
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message, type: 'error' } }));
                        }
                    });
                }
            }
        }
    </script>
</x-layout.app>
