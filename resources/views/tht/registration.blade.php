<x-layout.app title="Registrasi Pasien Baru">
    <div class="p-6 lg:p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Registration Form -->
            <div class="md:col-span-2">
                <x-ui.card class="overflow-hidden">
                    <div class="p-6 border-b border-neutral-200 bg-neutral-50 flex items-center gap-3">
                        <div class="p-2 bg-white rounded-lg border border-neutral-200 text-primary-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">Formulir Pendaftaran</h2>
                    </div>

                    <form action="{{ route('registration.store') }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="no_ktp" class="block text-sm font-bold text-slate-700 mb-2">No. KTP / NIK</label>
                                <div class="flex flex-row gap-2 items-stretch">
                                    <x-ui.input name="no_ktp" id="no_ktp" placeholder="Contoh: 32xxxxxxxxxxxxxx" class="flex-1 w-full" />
                                    <button type="button" onclick="checkNik()" id="btn-check-nik" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2.5 rounded-lg font-bold text-sm flex items-center justify-center gap-2 transition-colors whitespace-nowrap">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity w-4 h-4"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                                        Cek SatuSehat
                                    </button>
                                </div>
                                <span id="nik-status" class="text-xs font-bold mt-1 block"></span>
                            </div>

                            <div class="md:col-span-2">
                                <label for="nm_pasien" class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Pasien</label>
                                <x-ui.input name="nm_pasien" id="nm_pasien" required placeholder="Nama sesuai KTP" />
                            </div>

                            <div>
                                <label for="jk" class="block text-sm font-bold text-slate-700 mb-2">Jenis Kelamin</label>
                                <x-ui.select name="jk" id="jk">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </x-ui.select>
                            </div>

                            <div>
                                <label for="tgl_lahir" class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir</label>
                                <x-ui.input type="date" name="tgl_lahir" id="tgl_lahir" />
                            </div>

                            <div class="md:col-span-2">
                                <label for="ihs_id" class="block text-sm font-bold text-slate-700 mb-2">ID SatuSehat (IHS)</label>
                                <x-ui.input name="ihs_id" id="ihs_id" readonly class="bg-slate-100" placeholder="Otomatis terisi setelah Cek NIK" />
                            </div>
                        </div>

                        <script>
                            function checkNik() {
                                let nik = document.getElementById('no_ktp').value;
                                let btn = document.getElementById('btn-check-nik');
                                let status = document.getElementById('nik-status');
                                
                                if (!nik) {
                                    alert('Masukkan NIK terlebih dahulu!');
                                    return;
                                }
                                
                                btn.disabled = true;
                                btn.innerHTML = '<svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memeriksa...';
                                status.innerHTML = '';
                                status.className = 'text-xs font-bold mt-1 block';

                                fetch('/api/satusehat/patient/' + nik)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.found) {
                                            document.getElementById('nm_pasien').value = data.name;
                                            document.getElementById('jk').value = data.gender;
                                            document.getElementById('tgl_lahir').value = data.birthDate;
                                            document.getElementById('ihs_id').value = data.ihs_id;
                                            
                                            status.innerHTML = '✓ Pasien Ditemukan di SatuSehat';
                                            status.classList.remove('text-red-500', 'text-orange-500');
                                            status.classList.add('text-green-600');
                                        } else {
                                            status.innerHTML = '✕ Pasien Tidak Ditemukan';
                                            status.classList.remove('text-green-600', 'text-orange-500');
                                            status.classList.add('text-red-500');
                                        }
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        status.innerHTML = '⚠ Terjadi kesalahan koneksi';
                                        status.classList.add('text-orange-500');
                                    })
                                    .finally(() => {
                                        btn.disabled = false;
                                        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity w-4 h-4"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg> Cek SatuSehat';
                                    });
                            }
                        </script>

                        <div class="border-t border-neutral-100 pt-6">
                             <h3 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Layanan Tujuan</h3>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div>
                                    <label for="kd_dokter" class="block text-sm font-bold text-slate-700 mb-2">Dokter Tujuan</label>
                                    <x-ui.select name="kd_dokter" id="kd_dokter">
                                        @foreach($doctors as $doc)
                                            <option value="{{ $doc->kd_dokter }}">{{ $doc->nm_dokter }}</option>
                                        @endforeach
                                    </x-ui.select>
                                 </div>
                             </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 gap-3">
                            <x-ui.button variant="secondary" type="reset">Reset</x-ui.button>
                            <x-ui.button type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/></svg> Simpan & Masuk Antrian
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <!-- Stats / Info -->
            <div class="space-y-6">
                <x-ui.card class="p-6 text-center">
                    <div class="text-xs font-bold text-primary-600 uppercase tracking-widest mb-2">Antrian Saat Ini</div>
                    <div class="text-5xl font-bold text-slate-900 mb-2">A-{{ str_pad($registrations->count(), 3, '0', STR_PAD_LEFT) }}</div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span> Poli THT
                    </div>
                </x-ui.card>

                <!-- List Pasien -->
                <x-ui.card class="overflow-hidden">
                    <div class="p-4 border-b border-neutral-200 bg-neutral-50">
                        <h2 class="font-bold text-slate-800 text-sm">Pasien Hari Ini</h2>
                    </div>
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="w-full text-left text-sm relative">
                            <thead class="bg-white text-slate-500 font-medium border-b border-neutral-200 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 text-xs uppercase tracking-wider bg-neutral-50">No. Rawat</th>
                                    <th class="px-4 py-3 text-xs uppercase tracking-wider bg-neutral-50">Nama</th>
                                    <th class="px-4 py-3 text-xs uppercase tracking-wider text-right bg-neutral-50">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @forelse($registrations as $reg)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $reg->no_rawat }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $reg->pasien->nm_pasien }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <x-ui.badge variant="success">Terkirim</x-ui.badge>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-slate-400 text-xs italic">Belum ada pasien terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layout.app>
