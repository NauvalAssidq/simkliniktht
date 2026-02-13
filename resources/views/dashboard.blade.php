<x-layout.app title="Dashboard Overview">
    <div class="p-6 lg:p-8 h-full overflow-y-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-ui.card class="p-6">
                <div class="text-sm font-medium text-slate-500 mb-1">Total Pasien Hari Ini</div>
                <div class="text-3xl font-bold text-slate-900">24</div>
                <div class="text-xs text-green-600 flex items-center gap-1 mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg> +12% dari kemarin
                </div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <div class="text-sm font-medium text-slate-500 mb-1">Pasien Menunggu</div>
                <div class="text-3xl font-bold text-slate-900">8</div>
                <div class="text-xs text-slate-400 mt-2">Estimasi waktu tunggu: 15 mnt</div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <div class="text-sm font-medium text-slate-500 mb-1">Selesai Diperiksa</div>
                <div class="text-3xl font-bold text-slate-900">16</div>
            </x-ui.card>
        </div>

        <h3 class="font-bold text-lg text-slate-800 mb-4">Aktivitas Terbaru</h3>
        <x-ui.card class="overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 text-slate-500 font-medium border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-3">Pasien</th>
                        <th class="px-6 py-3">Poli</th>
                        <th class="px-6 py-3">Dokter</th>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-900">Budi Santoso</td>
                        <td class="px-6 py-4 text-slate-500">THT</td>
                        <td class="px-6 py-4 text-slate-500">dr. Spesialis</td>
                        <td class="px-6 py-4 text-slate-500">10:30</td>
                        <td class="px-6 py-4"><x-ui.badge variant="success">Selesai</x-ui.badge></td>
                    </tr>
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-900">Siti Aminah</td>
                        <td class="px-6 py-4 text-slate-500">THT</td>
                        <td class="px-6 py-4 text-slate-500">dr. Spesialis</td>
                        <td class="px-6 py-4 text-slate-500">10:45</td>
                        <td class="px-6 py-4"><x-ui.badge variant="warning">Menunggu</x-ui.badge></td>
                    </tr>
                </tbody>
            </table>
        </x-ui.card>
    </div>
</x-layout.app>
