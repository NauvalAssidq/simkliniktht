<x-layout.app title="Tambah Praktisi Lokal">
    <div class="p-6 lg:p-8 h-full overflow-y-auto">
        <div class="max-w-3xl mx-auto">
            <x-ui.card class="overflow-hidden mb-6">
                <div class="p-6 border-b border-neutral-200 bg-neutral-50 flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg border border-neutral-200 text-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Tambah Praktisi Baru</h2>
                </div>

                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <span class="font-bold">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                            <span class="font-bold">{{ session('error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('satusehat.practitioner.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="kd_dokter" class="block text-sm font-bold text-slate-700 mb-2">Kode Dokter (Local)</label>
                                <x-ui.input type="text" name="kd_dokter" id="kd_dokter" placeholder="Contoh: D001" value="{{ old('kd_dokter') }}" required />
                                @error('kd_dokter') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="nm_dokter" class="block text-sm font-bold text-slate-700 mb-2">Nama Dokter</label>
                                <x-ui.input type="text" name="nm_dokter" id="nm_dokter" placeholder="Nama Lengkap dengan Gelar" value="{{ old('nm_dokter') }}" required />
                                @error('nm_dokter') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="nik" class="block text-sm font-bold text-slate-700 mb-2">NIK (No. KTP)</label>
                                <x-ui.input type="text" name="nik" id="nik" placeholder="16 Digit NIK" maxlength="16" value="{{ old('nik') }}" required />
                                @error('nik') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="ihs_id" class="block text-sm font-bold text-slate-700 mb-2">SatuSehat ID (IHS Practitioner ID)</label>
                                <x-ui.input type="text" name="ihs_id" id="ihs_id" placeholder="Format: N1xxxxxxxx" value="{{ old('ihs_id') }}" required />
                                @error('ihs_id') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2 border-t border-slate-100 pt-4 mt-2">
                                <h3 class="text-sm font-bold text-slate-900 mb-4">Akun Login Dokter</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Email Login</label>
                                        <x-ui.input type="email" name="email" id="email" placeholder="dokter@simklinik.com" value="{{ old('email') }}" required />
                                        @error('email') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                                        <x-ui.input type="password" name="password" id="password" placeholder="Minimal 6 karakter" required />
                                        @error('password') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end gap-3">
                            <x-ui.button variant="secondary" type="reset">
                                Reset
                            </x-ui.button>
                            <x-ui.button type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Simpan Data
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layout.app>
