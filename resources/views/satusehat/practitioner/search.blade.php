<x-layout.app title="Cari Praktisi SatuSehat">
    <div class="p-6 lg:p-8 h-full overflow-y-auto">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden mb-6">
                <div class="p-6 border-b border-neutral-200 bg-neutral-50 flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg border border-neutral-200 text-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-stethoscope w-5 h-5"><path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/><path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/><circle cx="20" cy="10" r="2"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Cari Praktisi SatuSehat</h2>
                </div>

                <div class="p-6">
                    <form action="{{ route('satusehat.practitioner.search') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                            <div class="md:col-span-3">
                                <label for="search_type" class="block text-sm font-bold text-slate-700 mb-2">Tipe Pencarian</label>
                                <x-ui.select name="search_type" id="search_type">
                                    <option value="name" {{ (isset($search_type) && $search_type == 'name') ? 'selected' : '' }}>Nama (Name)</option>
                                    <option value="nik" {{ (isset($search_type) && $search_type == 'nik') ? 'selected' : '' }}>NIK (Identifier)</option>
                                </x-ui.select>
                            </div>
                            <div class="md:col-span-7">
                                <label for="search_query" class="block text-sm font-bold text-slate-700 mb-2">Kata Kunci</label>
                                <x-ui.input type="text" name="search_query" id="search_query" placeholder="Masukkan Nama atau NIK..." value="{{ $search_query ?? '' }}" required />
                            </div>
                            <div class="md:col-span-2">
                                <x-ui.button type="submit" class="w-full justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                    Curl It!
                                </x-ui.button>
                            </div>
                        </div>
                    </form>
                </div>
            </x-ui.card>

            @if(isset($result))
                <div class="space-y-6">
                    <x-ui.card class="overflow-hidden">
                        <div class="p-4 border-b border-neutral-200 bg-neutral-50">
                            <h2 class="font-bold text-slate-800 text-sm">Hasil Pencarian</h2>
                        </div>
                        
                        <div class="p-6">
                            @if($result['status'] == 'success')
                                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    <span class="font-bold">Request Berhasil</span>
                                </div>

                                @if(isset($result['response']['entry']) && count($result['response']['entry']) > 0)
                                    <div class="overflow-x-auto border border-neutral-200 rounded-lg mb-6">
                                        <table class="w-full text-left text-sm">
                                            <thead class="bg-neutral-50 text-slate-500 font-medium border-b border-neutral-200">
                                                <tr>
                                                    <th class="px-4 py-3 uppercase tracking-wider">Nama</th>
                                                    <th class="px-4 py-3 uppercase tracking-wider">IHS ID (Practitioner ID)</th>
                                                    <th class="px-4 py-3 uppercase tracking-wider">Gender</th>
                                                    <th class="px-4 py-3 uppercase tracking-wider text-right">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-neutral-100 bg-white">
                                                @foreach($result['response']['entry'] as $entry)
                                                    @php $res = $entry['resource']; @endphp
                                                    <tr class="hover:bg-neutral-50 transition-colors">
                                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $res['name'][0]['text'] ?? '-' }}</td>
                                                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $res['id'] ?? '-' }}</td>
                                                        <td class="px-4 py-3">{{ $res['gender'] ?? '-' }}</td>
                                                        <td class="px-4 py-3 text-right">
                                                            <button onclick="copyToClipboard('{{ $res['id'] ?? '' }}')" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                                                Copy ID
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-8 text-center border-2 border-dashed border-neutral-200 rounded-lg text-slate-400 mb-6">
                                        <p class="font-medium">Tidak ada data ditemukan.</p>
                                    </div>
                                @endif

                                <div class="bg-slate-900 rounded-lg overflow-hidden shadow-lg">
                                    <div class="px-4 py-2 bg-slate-800 border-b border-slate-700 flex justify-between items-center text-xs text-slate-400 font-mono">
                                        <span>Raw Response (Curl Output)</span>
                                        <span>JSON</span>
                                    </div>
                                    <pre class="p-4 text-xs font-mono text-green-400 overflow-x-auto max-h-96 custom-scrollbar">{{ json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            @else
                                <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                                    <span class="font-bold">Request Gagal: {{ $result['message'] ?? 'Unknown Error' }}</span>
                                </div>
                                @if(isset($result['response']))
                                    <div class="bg-red-50 rounded-lg overflow-hidden border border-red-100">
                                        <div class="px-4 py-2 bg-red-100 border-b border-red-200 text-xs text-red-800 font-bold font-mono">
                                            Raw Error Response
                                        </div>
                                        <pre class="p-4 text-xs font-mono text-red-900 overflow-x-auto">{{ json_encode($result['response'], JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </x-ui.card>
                </div>
            @endif
        </div>
    </div>

    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            let btn = event.target.closest('button');
            let originalContent = btn.innerHTML;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M20 6 9 17l-5-5"/></svg> Copied!';
            btn.classList.remove('bg-primary-100', 'text-primary-700');
            btn.classList.add('bg-green-100', 'text-green-700');
            
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.classList.add('bg-primary-100', 'text-primary-700');
                btn.classList.remove('bg-green-100', 'text-green-700');
            }, 2000);
        }, function(err) {
            console.error('Async: Could not copy text: ', err);
            alert('Gagal menyalin ID');
        });
    }
    </script>
</x-layout.app>
