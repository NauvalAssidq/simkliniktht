<x-ui.modal name="edit-practitioner-modal" :show="false" title="Edit Data Praktisi">
    <form :action="'{{ url('satusehat/praktisi') }}/' + editData.kd_dokter" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        
        <div class="p-3 bg-blue-50 text-blue-800 rounded-lg text-sm mb-4">
            Mengedit Dokter: <span class="font-bold" x-text="editData.nm_dokter"></span> (<span x-text="editData.kd_dokter"></span>)
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-forms.input label="NIK (KTP)" name="nik" required x-model="editData.nik" maxlength="16" />
            <x-forms.input label="SatuSehat IHS ID" name="ihs_id" required x-model="editData.ihs_id" />
            <x-forms.input label="Nama Lengkap" name="nm_dokter" required x-model="editData.nm_dokter" class="md:col-span-2" />
            <x-forms.input label="Email Login" name="email" type="email" required x-model="editData.email" />
            <x-forms.input label="Password Baru (Opsional)" name="password" type="password" placeholder="Kosongkan jika tidak ubah" />
        </div>
        
        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100 mt-4">
            <x-ui.secondary-button type="button" @click="$dispatch('close-modal', 'edit-practitioner-modal')">Batal</x-ui.secondary-button>
            <x-ui.primary-button type="submit">Simpan Perubahan</x-ui.primary-button>
        </div>
    </form>
</x-ui.modal>
