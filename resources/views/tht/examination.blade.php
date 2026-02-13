<x-layout.app title="Pemeriksaan Dokter THT">
    <div class="h-full flex flex-col lg:flex-row overflow-hidden">
        
        <!-- Queue List (Left Sidebar) -->
        <div class="w-full lg:w-96 bg-neutral-50 border-r border-neutral-200 flex flex-col z-20">
            <div class="p-6 border-b border-neutral-200 bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-slate-800">Antrian Pasien</h3>
                    <x-ui.badge variant="primary">{{ count($queue) }} Menunggu</x-ui.badge>
                </div>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-3 w-4 h-4 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <x-ui.input type="text" id="queue-search" placeholder="Cari nama pasien..." class="pl-9" />
                </div>
            </div>

            <div id="queue-list" class="flex-1 overflow-y-auto p-4 space-y-2">
                @foreach($queue as $q)
                    <div onclick="selectPatient('{{ $q->no_rawat }}', '{{ $q->pasien->nm_pasien }}', '{{ $q->no_rkm_medis }}')" 
                         class="flex items-center gap-3 p-3 bg-white rounded-lg border border-neutral-200 hover:border-primary-400 cursor-pointer transition-all">
                        <span class="bg-primary-50 text-primary-700 font-bold text-sm px-2.5 py-1 rounded-lg shrink-0">{{ $q->no_reg }}</span>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-slate-800 text-sm truncate">{{ $q->pasien->nm_pasien }}</h4>
                            <span class="text-[11px] text-slate-400">{{ $q->no_rkm_medis }}</span>
                        </div>
                        <button onclick="callPatient('{{ $q->no_rawat }}', event)" 
                                class="text-xs bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg font-bold flex items-center gap-1 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg> Panggil
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex-1 flex flex-col bg-white overflow-hidden relative">
            
            <div id="empty-state" class="flex-1 flex flex-col items-center justify-center text-slate-400 p-12 text-center">
                <div class="p-6 rounded-full bg-neutral-50 mb-6 border border-neutral-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 text-slate-300"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-600 mb-2">Siap Melakukan Pemeriksaan</h3>
                <p class="text-sm max-w-md mx-auto">Pilih pasien dari daftar antrian di sebelah kiri untuk memulai pemeriksaan medis.</p>
            </div>

            <div id="exam-container" class="hidden flex-1 flex flex-col h-full">
                <div class="bg-white px-8 py-6 border-b border-neutral-200 flex justify-between items-center shadow-[0_4px_20px_-2px_rgba(0,0,0,0.02)] z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-primary-500/20">
                            PS
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 leading-none mb-1" id="current-patient">Nama Pasien</h2>
                            <div class="flex items-center gap-3 text-sm font-medium text-slate-500">
                                <span class="bg-primary-50 text-primary-700 px-2 py-0.5 rounded text-xs font-bold border border-primary-100">RALAN</span>
                                <span>Pemeriksaan THT</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button variant="secondary" class="text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg> Riwayat
                        </x-ui.button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-8 bg-neutral-50/50">
                    <form id="exam-form" class="max-w-6xl mx-auto space-y-6">
                        @csrf
                        <input type="hidden" name="no_rawat" id="no_rawat">

                        <div class="grid grid-cols-2 gap-4">
                            <x-ui.card class="p-4 bg-white border border-primary-200">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tekanan Darah</div>
                                <div class="relative">
                                    <input type="text" name="tensi" class="block w-full border border-neutral-200 rounded-lg px-3 py-2 text-lg font-bold text-slate-800 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none placeholder-slate-300 transition-all" placeholder="0/0">
                                    <span class="absolute right-2 top-2.5 text-xs font-bold text-slate-400 pointer-events-none">mmHg</span>
                                </div>
                            </x-ui.card>
                            <x-ui.card class="p-4 bg-white border border-primary-200">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Suhu Tubuh</div>
                                <div class="relative">
                                    <input type="text" name="suhu_tubuh" class="block w-full border border-neutral-200 rounded-lg px-3 py-2 text-lg font-bold text-slate-800 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none placeholder-slate-300 transition-all" placeholder="00.0">
                                    <span class="absolute right-2 top-2.5 text-xs font-bold text-slate-400 pointer-events-none">°C</span>
                                </div>
                            </x-ui.card>
                        </div>

                        <!-- Status Audiologi -->
                        <x-ui.card class="p-4 bg-white border border-neutral-200 shadow-sm">
                            <div class="px-5 py-3 border-b border-neutral-100 -mx-4 -mt-4 mb-4 bg-neutral-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500"><path d="M2 10v3"/><path d="M6 6v11"/><path d="M10 3v18"/><path d="M14 8v7"/><path d="M18 5v13"/><path d="M22 10v3"/></svg>
                                <h3 class="font-bold text-slate-700 text-sm">Status Audiologi</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Telinga Kanan -->
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">R</div>
                                        <span class="text-sm font-bold text-slate-700">Telinga Kanan (AD)</span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Tipe Gangguan</label>
                                        <x-ui.select name="tipe_gangguan_kanan" class="w-full">
                                            <option value="">Normal / Tidak Ada</option>
                                            <option value="Conductive">Conductive Hearing Loss</option>
                                            <option value="Sensorineural">Sensorineural Hearing Loss</option>
                                            <option value="Mixed">Mixed Hearing Loss</option>
                                        </x-ui.select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Ambang Dengar (dB)</label>
                                        <div class="relative">
                                            <x-ui.input type="number" step="0.1" name="ambang_dengar_kanan" placeholder="0.0" />
                                            <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400 pointer-events-none">dB</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Telinga Kiri -->
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold">L</div>
                                        <span class="text-sm font-bold text-slate-700">Telinga Kiri (AS)</span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Tipe Gangguan</label>
                                        <x-ui.select name="tipe_gangguan_kiri" class="w-full">
                                            <option value="">Normal / Tidak Ada</option>
                                            <option value="Conductive">Conductive Hearing Loss</option>
                                            <option value="Sensorineural">Sensorineural Hearing Loss</option>
                                            <option value="Mixed">Mixed Hearing Loss</option>
                                        </x-ui.select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Ambang Dengar (dB)</label>
                                        <div class="relative">
                                            <x-ui.input type="number" step="0.1" name="ambang_dengar_kiri" placeholder="0.0" />
                                            <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400 pointer-events-none">dB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Left Col -->
                            <div class="space-y-6">
                                <x-ui.card class="overflow-hidden border border-neutral-200 ">
                                    <div class="px-5 py-3 border-b border-neutral-100 bg-white flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs ring-1 ring-blue-100">S</div>
                                        <h3 class="font-bold text-slate-700 text-sm">Subjective (Keluhan)</h3>
                                    </div>
                                    <textarea name="keluhan" rows="4" class="w-full border-none p-5 text-sm text-slate-700 focus:ring-0 resize-none placeholder-slate-300" placeholder="Tulis keluhan pasien..."></textarea>
                                </x-ui.card>

                                <x-ui.card class="overflow-hidden border border-neutral-200 ">
                                    <div class="px-5 py-3 border-b border-neutral-100 bg-white flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-xs ring-1 ring-orange-100">O</div>
                                        <h3 class="font-bold text-slate-700 text-sm">Objective (Pemeriksaan)</h3>
                                    </div>
                                    <textarea name="pemeriksaan" rows="4" class="w-full border-none p-5 text-sm text-slate-700 focus:ring-0 resize-none placeholder-slate-300" placeholder="Hasil pemeriksaan fisik..."></textarea>
                                </x-ui.card>
                            </div>

                            <!-- Right Col -->
                            <div class="space-y-6">
                                <x-ui.card class="overflow-hidden border border-neutral-200 ">
                                    <div class="px-5 py-3 border-b border-neutral-100 bg-white flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs ring-1 ring-purple-100">A</div>
                                        <h3 class="font-bold text-slate-700 text-sm">Assessment (Diagnosa)</h3>
                                    </div>
                                    <div class="p-5 space-y-4">
                                        <textarea name="penilaian" rows="2" class="w-full border border-neutral-200 rounded-lg p-3 text-sm text-slate-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none placeholder-slate-300 resize-none transition-all" placeholder="Diagnosa Kerja..."></textarea>
                                        
                                        <div class="relative">
                                            <label class="block text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wider">ICD-10 (Optional)</label>
                                            <select id="search-diagnosis" class="w-full"></select>
                                        </div>
                                        <div id="selected-diagnosis" class="flex flex-wrap gap-2"></div>
                                    </div>
                                </x-ui.card>

                                <x-ui.card class="overflow-hidden border border-neutral-200">
                                    <div class="px-5 py-3 border-b border-neutral-100 bg-white flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs ring-1 ring-emerald-100">P</div>
                                        <h3 class="font-bold text-slate-700 text-sm">Plan (Tindakan & Resep)</h3>
                                    </div>
                                    <div class="p-5 space-y-4">
                                        <textarea name="instruksi" rows="2" class="w-full border border-neutral-200 rounded-lg p-3 text-sm text-slate-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none placeholder-slate-300 resize-none transition-all" placeholder="Instruksi / Resep..."></textarea>
                                        
                                        <div class="relative">
                                            <label class="block text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Tindakan / Prosedur</label>
                                            <select id="search-procedure" class="w-full"></select>
                                        </div>
                                        <div id="selected-procedures" class="flex flex-wrap gap-2"></div>
                                    </div>
                                </x-ui.card>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="p-4 bg-white border-t border-neutral-200 flex justify-end gap-3 sticky bottom-0 z-30 shadow-[0_-4px_20px_-2px_rgba(0,0,0,0.03)]">
                    <x-ui.button variant="secondary">Batal</x-ui.button>
                    <x-ui.button onclick="submitExam()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/></svg> Simpan Pemeriksaan
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



    <script>
        let diagnoses = []; // Array of { code, name, laterality: 'Both'|'Kanan'|'Kiri' }
        let procedures = []; // Array of { code, name, laterality: 'Both'|'Kanan'|'Kiri' }

        function selectPatient(noRawat, nmPasien, noRm) {
            $('#no_rawat').val(noRawat);
            $('#current-patient').text(nmPasien);
            let initials = nmPasien.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
            $('#current-patient').closest('.flex').find('.rounded-2xl').text(initials);
            $('#exam-container').removeClass('hidden');
            $('#empty-state').addClass('hidden');
            
            // Reset all inputs
            $('input[name="tensi"]').val('');
            $('input[name="suhu_tubuh"]').val('');
            $('textarea[name="keluhan"]').val('');
            $('textarea[name="pemeriksaan"]').val('');
            $('textarea[name="penilaian"]').val('');
            $('textarea[name="instruksi"]').val('');
            
            // Reset Audiology
            $('select[name="tipe_gangguan_kanan"]').val('');
            $('input[name="ambang_dengar_kanan"]').val('');
            $('select[name="tipe_gangguan_kiri"]').val('');
            $('input[name="ambang_dengar_kiri"]').val('');

            diagnoses = [];
            procedures = [];
            renderBadges();

            $.ajax({
                url: '/pemeriksaan/data?no_rawat=' + encodeURIComponent(noRawat),
                success: function(data) {
                    if (data.soap) {
                        $('input[name="tensi"]').val(data.soap.tensi);
                        $('input[name="suhu_tubuh"]').val(data.soap.suhu_tubuh);
                        $('textarea[name="keluhan"]').val(data.soap.keluhan);
                        $('textarea[name="pemeriksaan"]').val(data.soap.pemeriksaan);
                        $('textarea[name="penilaian"]').val(data.soap.penilaian);
                        $('textarea[name="instruksi"]').val(data.soap.instruksi);
                    }
                    
                    if (data.audiologi) {
                        $('select[name="tipe_gangguan_kanan"]').val(data.audiologi.tipe_gangguan_kanan);
                        $('input[name="ambang_dengar_kanan"]').val(data.audiologi.ambang_dengar_kanan);
                        $('select[name="tipe_gangguan_kiri"]').val(data.audiologi.tipe_gangguan_kiri);
                        $('input[name="ambang_dengar_kiri"]').val(data.audiologi.ambang_dengar_kiri);
                    }

                    if (data.diagnosa) {
                        data.diagnosa.forEach(d => {
                            addDiagnosis(d.kd_penyakit, d.kd_penyakit + ' - ' + d.nm_penyakit);
                            if(d.laterality) updateLaterality('diagnosis', d.kd_penyakit, d.laterality);
                        });
                    }
                    if (data.procedures) {
                        data.procedures.forEach(p => {
                            addProcedure(p.kd_jenis_prw, p.nm_perawatan + ' (' + p.total_byr + ')');
                            if(p.laterality) updateLaterality('procedure', p.kd_jenis_prw, p.laterality);
                        });
                    }
                }
            });
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        window.callPatient = function(noRawat, event) {
            event.stopPropagation();
            var btn = event.currentTarget;
            var originalText = btn.innerHTML;
            btn.innerHTML = 'Memanggil...';
            btn.disabled = true;

            $.ajax({
                url: '/antrian/call',
                method: 'POST',
                data: {
                    no_rawat: noRawat
                },
                success: function(response) {
                    btn.innerHTML = '✓ Dipanggil';
                    btn.classList.remove('bg-primary-600');
                    btn.classList.add('bg-green-600');
                    setTimeout(function() {
                        btn.innerHTML = originalText;
                        btn.classList.remove('bg-green-600');
                        btn.classList.add('bg-primary-600');
                        btn.disabled = false;
                    }, 2000);
                },
                error: function(xhr) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('Gagal memanggil pasien: ' + (xhr.responseJSON?.message || xhr.status));
                }
            });
        }

        $(document).ready(function() {
            $('#search-diagnosis').select2({
                width: '100%',
                placeholder: 'Cari ICD-10...',
                ajax: {
                    url: '/api/search/diagnosis',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data.map(function(item) {
                                return { id: item.kd_penyakit, text: item.kd_penyakit + ' - ' + item.nm_penyakit };
                            })
                        };
                    },
                    cache: true
                }
            });

            let searchTimeout;
            $('#queue-search').on('input', function() {
                clearTimeout(searchTimeout);
                let query = $(this).val();
                
                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: '{{ route("pemeriksaan.queue") }}',
                        data: { q: query },
                        success: function(data) {
                            let html = '';
                            if(data.length > 0) {
                                data.forEach(function(q) {
                                    html += `
                                    <div onclick="selectPatient('${q.no_rawat}', '${q.pasien.nm_pasien}', '${q.no_rkm_medis}')" 
                                         class="flex items-center gap-3 p-3 bg-white rounded-lg border border-neutral-200 hover:border-primary-400 cursor-pointer transition-all">
                                        <span class="bg-primary-50 text-primary-700 font-bold text-sm px-2.5 py-1 rounded-lg shrink-0">${q.no_reg}</span>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-slate-800 text-sm truncate">${q.pasien.nm_pasien}</h4>
                                            <span class="text-[11px] text-slate-400">${q.no_rkm_medis}</span>
                                        </div>
                                        <button onclick="callPatient('${q.no_rawat}', event)" 
                                                class="text-xs bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg font-bold flex items-center gap-1 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg> Panggil
                                        </button>
                                    </div>`;
                                });
                            } else {
                                html = `<div class="p-8 text-center text-slate-400">Tidak ada pasien ditemukan</div>`;
                            }
                            
                            $('#queue-list').html(html);
                        }
                    });
                }, 300); 
            });

            $('#search-diagnosis').on('select2:select', function (e) {
                var data = e.params.data;
                addDiagnosis(data.id, data.text);
                $(this).val(null).trigger('change');
            });

            $('#search-procedure').select2({
                width: '100%',
                placeholder: 'Cari Tindakan...',
                ajax: {
                    url: '/api/search/procedures',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data.map(function(item) {
                                return { id: item.kd_jenis_prw, text: item.nm_perawatan + ' (' + item.total_byr + ')' };
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#search-procedure').on('select2:select', function (e) {
                var data = e.params.data;
                addProcedure(data.id, data.text);
                $(this).val(null).trigger('change');
            });
        });

        function addDiagnosis(code, name) {
            if (diagnoses.some(d => d.code === code)) return;
            diagnoses.push({ code: code, name: name, laterality: 'Kedua' }); // Default 'Kedua'
            renderBadges();
        }

        function addProcedure(code, name) {
             if (procedures.some(p => p.code === code)) return;
            procedures.push({ code: code, name: name, laterality: 'Kedua' });
            renderBadges();
        }

        function updateLaterality(type, code, side) {
            if (type === 'diagnosis') {
                let d = diagnoses.find(x => x.code === code);
                if(d) d.laterality = side;
            } else {
                let p = procedures.find(x => x.code === code);
                if(p) p.laterality = side;
            }
            renderBadges();
        }

        function renderBadges() {
            const latBtn = (type, code, current, side, label, color) => `
                <button onclick="updateLaterality('${type}', '${code}', '${side}')" type="button" 
                    class="px-1.5 py-0.5 rounded text-[10px] ${current === side ? `bg-${color}-600 text-white` : `bg-white text-${color}-600 hover:bg-${color}-100`}">
                    ${label}
                </button>
            `;

            $('#selected-diagnosis').html(diagnoses.map(d => 
                `<div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 border border-purple-200 text-xs font-bold mr-2 px-2.5 py-1.5 rounded-lg shadow-sm">
                    <span>${d.name}</span>
                    <div class="flex bg-purple-100 rounded p-0.5 gap-0.5">
                        ${latBtn('diagnosis', d.code, d.laterality, 'Kanan', 'R', 'purple')}
                        ${latBtn('diagnosis', d.code, d.laterality, 'Kiri', 'L', 'purple')}
                        ${latBtn('diagnosis', d.code, d.laterality, 'Kedua', 'B', 'purple')}
                    </div>
                    <button onclick="removeDiag('${d.code}')" class="ml-1 hover:text-purple-900"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                </div>`
            ).join(''));
            
            $('#selected-procedures').html(procedures.map(p => 
                `<div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold mr-2 px-2.5 py-1.5 rounded-lg shadow-sm">
                    <span>${p.name}</span>
                    <div class="flex bg-emerald-100 rounded p-0.5 gap-0.5">
                        ${latBtn('procedure', p.code, p.laterality, 'Kanan', 'R', 'emerald')}
                        ${latBtn('procedure', p.code, p.laterality, 'Kiri', 'L', 'emerald')}
                        ${latBtn('procedure', p.code, p.laterality, 'Kedua', 'B', 'emerald')}
                    </div>
                    <button onclick="removeProc('${p.code}')" class="ml-1 hover:text-emerald-900"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                </div>`
            ).join(''));
        }

        window.removeDiag = function(code) {
            diagnoses = diagnoses.filter(d => d.code !== code);
            renderBadges();
        }

        window.removeProc = function(code) {
            procedures = procedures.filter(p => p.code !== code);
            renderBadges();
        }

        window.submitExam = function() {
            let formData = {
                no_rawat: $('#no_rawat').val(),
                tensi: $('input[name="tensi"]').val(),
                suhu_tubuh: $('input[name="suhu_tubuh"]').val(),
                keluhan: $('textarea[name="keluhan"]').val(),
                pemeriksaan: $('textarea[name="pemeriksaan"]').val(),
                penilaian: $('textarea[name="penilaian"]').val(),
                instruksi: $('textarea[name="instruksi"]').val(),
                audiologi: {
                    tipe_gangguan_kanan: $('select[name="tipe_gangguan_kanan"]').val(),
                    ambang_dengar_kanan: $('input[name="ambang_dengar_kanan"]').val(),
                    tipe_gangguan_kiri: $('select[name="tipe_gangguan_kiri"]').val(),
                    ambang_dengar_kiri: $('input[name="ambang_dengar_kiri"]').val()
                },
                diagnosa: diagnoses,
                procedures: procedures,
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: '/pemeriksaan/store',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert('Pemeriksaan berhasil disimpan!');
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        }
    </script>
</x-layout.app>
