<x-ui.modal name="search-ss-modal" :show="false" title="Cari Praktisi di SatuSehat" maxWidth="2xl">
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <select x-model="searchType" class="shrink-0 w-28 rounded-lg p-2 border border-neutral-300 text-sm font-medium text-neutral-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                <option value="name">Nama</option>
                <option value="nik">NIK</option>
            </select>
            <input type="text" x-model="searchQuery" @keydown.enter="performSearch()" placeholder="Masukkan kata kunci..." class="flex-1 min-w-0 rounded-lg p-2 border border-neutral-300 focus:border-primary-500 focus:ring-primary-500" />
            <x-ui.primary-button @click="performSearch()" ::disabled="searching || searchQuery.length < 3" class="shrink-0">
                <span x-show="!searching">Cari</span>
                <span x-show="searching">...</span>
            </x-ui.primary-button>
        </div>

        <!-- Results Area -->
        <div class="min-h-[200px] max-h-[400px] overflow-y-auto bg-neutral-50 rounded-lg border border-neutral-200 p-2">
            <template x-if="!searchResults && !searching">
                <div class="text-center text-neutral-400 py-8">Hasil pencarian akan muncul di sini.</div>
            </template>
            <template x-if="searching">
                <div class="flex justify-center py-8">
                    <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </template>
            
            <template x-if="searchResults && searchResults.data">
                <div class="space-y-3">
                    <template x-for="practitioner in searchResults.data" :key="practitioner.ihs_id">
                        <x-ui.card class="p-2 flex justify-between items-start border border-neutral-200 shadow-sm">
                            <div>
                                <div class="font-bold text-neutral-800" x-text="practitioner.name"></div>
                                <div class="text-xs text-neutral-500 mt-1">
                                    NIK: <span x-text="practitioner.nik"></span>
                                </div>
                                <div class="text-xs text-neutral-500 mt-1">
                                    IHS ID: <span class="font-mono bg-neutral-100 px-1 rounded" x-text="practitioner.ihs_id"></span>
                                </div>
                            </div>
                            <x-ui.secondary-button @click="
                                $dispatch('open-modal', 'create-practitioner-modal');
                                $dispatch('close-modal', 'search-ss-modal');
                                // Pre-fill Create Form
                                setTimeout(() => {
                                    document.querySelector('[name=nm_dokter]').value = practitioner.name;
                                    document.querySelector('[name=nik]').value = practitioner.nik;
                                    document.querySelector('[name=ihs_id]').value = practitioner.ihs_id;
                                }, 200);
                            " class="!px-3 !py-1 text-xs">
                                Pilih
                            </x-ui.secondary-button>
                        </x-ui.card>
                    </template>
                </div>
            </template>
             <template x-if="searchResults && searchResults.data && searchResults.data.length === 0">
                <div class="text-center text-neutral-500 py-8">Tidak ditemukan data.</div>
            </template>
        </div>
    </div>
</x-ui.modal>
