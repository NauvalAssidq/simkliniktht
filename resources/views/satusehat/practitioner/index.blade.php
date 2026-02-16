<x-layout.app>
    <x-slot:title>Manajemen Praktisi (Dokter)</x-slot:title>

    <div class="p-6 lg:p-8" x-data="{ 
        searchModalOpen: false, 
        searchResults: null,
        searching: false,
        searchQuery: '',
        searchType: 'name',
        
        createModalOpen: false,
        
        editModalOpen: false,
        editData: {},
        
        deleteModalOpen: false,
        deleteUrl: '',

        performSearch() {
            this.searching = true;
            this.searchResults = null;
            
            fetch('{{ route('satusehat.practitioner.search') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    search_type: this.searchType,
                    search_query: this.searchQuery
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(result => {
                console.log('Raw API result:', result);
                // Transform FHIR response to flat format for the modal template
                if (result.status === 'success' && result.response && result.response.entry) {
                    const mapped = result.response.entry.map(entry => {
                        const res = entry.resource;
                        // Extract NIK from identifiers
                        let nik = '-';
                        if (res.identifier) {
                            const nikId = res.identifier.find(id => id.system && id.system.includes('nik'));
                            if (nikId) nik = nikId.value;
                        }
                        return {
                            ihs_id: res.id || '-',
                            name: (res.name && res.name[0] && res.name[0].text) || '-',
                            nik: nik
                        };
                    });
                    this.searchResults = { data: mapped };
                } else {
                    // No results or failed
                    this.searchResults = { data: [] };
                }
                this.searching = false;
            })
            .catch(error => {
                console.error('Search Error:', error);
                this.searching = false;
                alert('Terjadi kesalahan saat mencari: ' + error.message);
            });
        }
    }">

        <!-- Practitioner List -->
        <x-ui.card class="overflow-hidden">
            {{-- Row 1: Title + Action Buttons --}}
            <div class="px-6 py-5 border-b border-neutral-200 bg-neutral-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg border border-neutral-200 text-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Daftar Praktisi</h2>
                        <p class="text-xs text-slate-500">Kelola data dokter dan sinkronisasi SatuSehat</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.secondary-button @click="$dispatch('open-modal', 'search-ss-modal')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Cari di SatuSehat
                    </x-ui.secondary-button>
                    <x-ui.primary-button @click="$dispatch('open-modal', 'create-practitioner-modal')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Tambah Praktisi
                    </x-ui.primary-button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-slate-500 font-medium border-b border-neutral-200">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold">Nama & Kode</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold">IHS ID (SatuSehat)</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold">NIK & Email</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold">Status</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($practitioners as $doc)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $doc->nm_dokter }}</div>
                                <div class="text-xs font-mono text-slate-500 mt-0.5">{{ $doc->kd_dokter }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($doc->satuSehatMapping)
                                <x-ui.badge variant="success" class="font-mono">
                                    {{ $doc->satuSehatMapping->ihs_practitioner_id }}
                                </x-ui.badge>
                                @else
                                <span class="text-xs text-slate-400 italic">Belum Terhubung</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700 text-xs font-medium">{{ $doc->no_ktp }}</div>
                                @php $user = \App\Models\User::where('kd_dokter', $doc->kd_dokter)->first(); @endphp
                                <div class="text-xs text-slate-500 mt-0.5">{{ $user ? $user->email : '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge variant="{{ $doc->status == '1' ? 'primary' : 'neutral' }}">
                                    {{ $doc->status == '1' ? 'Aktif' : 'Non-Aktif' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        @click="
                                            editData = {
                                                kd_dokter: '{{ $doc->kd_dokter }}',
                                                nm_dokter: '{{ addslashes($doc->nm_dokter) }}',
                                                nik: '{{ $doc->no_ktp }}',
                                                ihs_id: '{{ $doc->satuSehatMapping ? $doc->satuSehatMapping->ihs_practitioner_id : '' }}',
                                                email: '{{ $user ? $user->email : '' }}'
                                            };
                                            $dispatch('open-modal', 'edit-practitioner-modal');
                                        "
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button 
                                        @click="
                                            deleteUrl = '{{ route('satusehat.practitioner.destroy', $doc->kd_dokter) }}';
                                            $dispatch('open-modal', 'delete-practitioner-modal');
                                        "
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                        title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 w-8 h-8"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                    <p>Belum ada data praktisi.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-neutral-200 bg-neutral-50">
                {{ $practitioners->links() }}
            </div>
        </x-ui.card>

        <!-- Create Modal -->
        @include('satusehat.practitioner.modal.create-modal')

        <!-- Edit Modal -->
        @include('satusehat.practitioner.modal.edit-modal')

        <!-- Delete Modal -->
        <x-ui.confirm-delete-modal name="delete-practitioner-modal" :action="'#'" x-bind:action="deleteUrl" title="Hapus Praktisi" message="Apakah Anda yakin ingin menghapus praktisi ini? Akun login dan mapping SatuSehat juga akan dihapus." />

        <!-- Search SatuSehat Modal -->
        @include('satusehat.practitioner.modal.search-modal')

    </div>
</x-layout.app>
