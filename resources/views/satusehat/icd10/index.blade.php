<x-layout.app>
    <x-slot:title>Manajemen ICD-10</x-slot:title>

    <div class="p-6 lg:p-8" x-data="{
        addModalOpen: false,
        editModalOpen: false,
        editData: {},
        deleteModalOpen: false,
        deleteUrl: ''
    }">

        {{-- Success / Error Flash --}}
        @if(session('success'))
            <div class="mb-4 p-4 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-medium flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-lg bg-rose-50 text-rose-800 border border-rose-200 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <x-ui.card class="overflow-hidden">
            {{-- Row 1: Title + Action Buttons --}}
            <div class="p-6 border-b border-neutral-200 bg-neutral-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg border border-neutral-200 text-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Kode ICD-10</h2>
                        <p class="text-xs text-slate-500">{{ $icd10->total() }} kode tersedia</p>
                    </div>
                </div>
                <x-ui.primary-button @click="$dispatch('open-modal', 'add-icd10-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Manual
                </x-ui.primary-button>
            </div>

            {{-- Row 2: Search + Import Toolbar --}}
            <div class="px-6 py-3 border-b border-neutral-100 bg-white flex flex-wrap items-center justify-between gap-3">
                {{-- Search --}}
                <form method="GET" action="{{ route('satusehat.icd10') }}" class="flex items-center gap-2" onsubmit="if(!this.search.value.trim()){this.search.disabled=true;}">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama penyakit..." class="rounded-lg pl-9 pr-3 py-2 border border-neutral-300 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none w-72" />
                    </div>
                    <x-ui.secondary-button type="submit">Cari</x-ui.secondary-button>
                    @if(request('search'))
                        <a href="{{ route('satusehat.icd10') }}" class="text-xs text-slate-400 hover:text-slate-600 transition-colors">✕ Reset</a>
                    @endif
                </form>

                {{-- CSV Import --}}
                <form method="POST" action="{{ route('satusehat.icd10.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-dashed border-neutral-300 bg-neutral-50 text-sm text-slate-500 hover:bg-white hover:border-primary-300 hover:text-primary-600 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <span id="csv-label">Import CSV</span>
                        <input type="file" name="csv_file" accept=".csv,.txt" class="hidden" onchange="document.getElementById('csv-label').textContent = this.files[0]?.name || 'Import CSV'; document.getElementById('import-btn').classList.remove('hidden');" />
                    </label>
                    <x-ui.primary-button type="submit" id="import-btn" class="hidden">
                        Upload
                    </x-ui.primary-button>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-slate-500 font-medium border-b border-neutral-200">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold w-36">Kode</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold">Nama Penyakit</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold w-32">Versi</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold text-right w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($icd10 as $item)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-3">
                                <span class="font-mono font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded text-xs">{{ $item->kd_penyakit }}</span>
                            </td>
                            <td class="px-6 py-3 text-slate-700">{{ $item->nm_penyakit }}</td>
                            <td class="px-6 py-3 text-slate-500 text-xs">{{ $item->version ?? '-' }}</td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="
                                            editData = {
                                                kd_penyakit: '{{ $item->kd_penyakit }}',
                                                nm_penyakit: '{{ addslashes($item->nm_penyakit) }}',
                                                version: '{{ addslashes($item->version ?? '') }}'
                                            };
                                            $dispatch('open-modal', 'edit-icd10-modal');
                                        "
                                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('satusehat.icd10.destroy', $item->kd_penyakit) }}" onsubmit="return confirm('Hapus kode {{ $item->kd_penyakit }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 w-8 h-8"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                                    <p>Belum ada data ICD-10. Import CSV atau tambah manual.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-neutral-200 bg-neutral-50">
                {{ $icd10->links() }}
            </div>
        </x-ui.card>

        {{-- Add Modal --}}
        <x-ui.modal name="add-icd10-modal" :show="false" title="Tambah Kode ICD-10">
            <form method="POST" action="{{ route('satusehat.icd10.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-forms.input label="Kode ICD-10" name="kd_penyakit" required placeholder="contoh: A00.0" maxlength="15" />
                    <x-forms.input label="Versi" name="version" placeholder="contoh: ICD-10 2019" maxlength="50" />
                    <div class="md:col-span-2">
                        <x-forms.input label="Nama Penyakit" name="nm_penyakit" required placeholder="contoh: Cholera due to Vibrio cholerae" />
                    </div>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100 mt-4">
                    <x-ui.secondary-button type="button" @click="$dispatch('close-modal', 'add-icd10-modal')">Batal</x-ui.secondary-button>
                    <x-ui.primary-button type="submit">Simpan</x-ui.primary-button>
                </div>
            </form>
        </x-ui.modal>

        {{-- Edit Modal --}}
        <x-ui.modal name="edit-icd10-modal" :show="false" title="Edit Kode ICD-10">
            <form :action="'{{ url('satusehat/icd10') }}/' + editData.kd_penyakit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="p-3 bg-blue-50 text-blue-800 rounded-lg text-sm mb-4">
                    Kode: <span class="font-bold font-mono" x-text="editData.kd_penyakit"></span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-forms.input label="Nama Penyakit" name="nm_penyakit" required x-model="editData.nm_penyakit" />
                    </div>
                    <x-forms.input label="Versi" name="version" x-model="editData.version" />
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100 mt-4">
                    <x-ui.secondary-button type="button" @click="$dispatch('close-modal', 'edit-icd10-modal')">Batal</x-ui.secondary-button>
                    <x-ui.primary-button type="submit">Simpan Perubahan</x-ui.primary-button>
                </div>
            </form>
        </x-ui.modal>

    </div>
</x-layout.app>
