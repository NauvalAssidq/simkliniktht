<x-ui.modal name="create-practitioner-modal" :show="false" title="Tambah Praktisi Baru">
    <form action="{{ route('satusehat.practitioner.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-forms.input label="Kode Dokter (Manual)" name="kd_dokter" required placeholder="Contoh: D001" />
            <x-forms.input label="NIK (KTP)" name="nik" required placeholder="16 Digit NIK" maxlength="16" />
            <x-forms.input label="Nama Lengkap" name="nm_dokter" required placeholder="Nama lengkap dengan gelar" class="md:col-span-2" />
            <x-forms.input label="SatuSehat IHS ID" name="ihs_id" required placeholder="1000xxxxxx" />
            <x-forms.input label="Email Login" name="email" type="email" required />
            <x-forms.input label="Password Login" name="password" type="password" required />
        </div>
        <!-- Hidden Status -->
        <input type="hidden" name="status" value="1">
        
        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100 mt-4">
            <x-ui.secondary-button type="button" @click="$dispatch('close-modal', 'create-practitioner-modal')">Batal</x-ui.secondary-button>
            <x-ui.primary-button type="submit">Simpan Data</x-ui.primary-button>
        </div>
    </form>
</x-ui.modal>
