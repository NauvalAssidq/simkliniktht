<x-layout.app>
    <x-slot:title>{{ $role === 'pendaftaran' ? 'Manajemen Resepsionis' : 'Manajemen Apoteker' }}</x-slot:title>

    <div class="p-6 lg:p-8" x-data="{ 
        createModalOpen: false,
        editModalOpen: false,
        editData: {},
        deleteModalOpen: false,
        deleteUrl: ''
    }">

        <x-ui.card class="overflow-hidden">
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-neutral-200 bg-neutral-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg border border-neutral-200 text-primary-600">
                        @if($role === 'pendaftaran')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/></svg>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $role === 'pendaftaran' ? 'Daftar Resepsionis' : 'Daftar Apoteker' }}</h2>
                        <p class="text-xs text-slate-500">Kelola akun {{ $role === 'pendaftaran' ? 'petugas pendaftaran' : 'petugas farmasi' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Role Tabs --}}
                    <a href="{{ route('staff.index', ['role' => 'pendaftaran']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all {{ $role === 'pendaftaran' ? 'bg-primary-50 text-primary-600 border-primary-300' : 'bg-white text-slate-500 border-neutral-200 hover:bg-slate-50' }}">
                        Resepsionis
                    </a>
                    <a href="{{ route('staff.index', ['role' => 'apotek']) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all {{ $role === 'apotek' ? 'bg-primary-50 text-primary-600 border-primary-300' : 'bg-white text-slate-500 border-neutral-200 hover:bg-slate-50' }}">
                        Apoteker
                    </a>
                    <x-ui.primary-button @click="$dispatch('open-modal', 'create-staff-modal')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-1"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Tambah {{ $role === 'pendaftaran' ? 'Resepsionis' : 'Apoteker' }}
                    </x-ui.primary-button>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-slate-500 font-medium border-b border-neutral-200">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold">Nama</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold">Email</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold">Role</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($staff as $user)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">ID: {{ $user->id }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <x-ui.badge variant="{{ $user->role === 'pendaftaran' ? 'primary' : 'success' }}">
                                    {{ $user->role === 'pendaftaran' ? 'Resepsionis' : 'Apoteker' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        @click="
                                            editData = {
                                                id: {{ $user->id }},
                                                name: '{{ addslashes($user->name) }}',
                                                email: '{{ $user->email }}',
                                                role: '{{ $user->role }}'
                                            };
                                            $dispatch('open-modal', 'edit-staff-modal');
                                        "
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button 
                                        @click="
                                            deleteUrl = '{{ route('staff.destroy', $user->id) }}';
                                            $dispatch('open-modal', 'delete-staff-modal');
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
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300 w-8 h-8"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                    <p>Belum ada data {{ $role === 'pendaftaran' ? 'resepsionis' : 'apoteker' }}.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-neutral-200 bg-neutral-50">
                {{ $staff->links() }}
            </div>
        </x-ui.card>

        {{-- Create Modal --}}
        <x-ui.modal name="create-staff-modal" title="Tambah {{ $role === 'pendaftaran' ? 'Resepsionis' : 'Apoteker' }}">
            <form method="POST" action="{{ route('staff.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="{{ $role }}">
                <div>
                    <label for="create-name" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <x-ui.input type="text" name="name" id="create-name" required />
                </div>
                <div>
                    <label for="create-email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <x-ui.input type="email" name="email" id="create-email" required />
                </div>
                <div>
                    <label for="create-password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <x-ui.input type="password" name="password" id="create-password" required minlength="6" />
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <x-ui.secondary-button type="button" @click="$dispatch('close-modal', 'create-staff-modal')">Batal</x-ui.secondary-button>
                    <x-ui.primary-button type="submit">Simpan</x-ui.primary-button>
                </div>
            </form>
        </x-ui.modal>

        {{-- Edit Modal --}}
        <x-ui.modal name="edit-staff-modal" title="Edit Staff">
            <form method="POST" x-bind:action="'/staff/' + editData.id" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="role" x-bind:value="editData.role">
                <div>
                    <label for="edit-name" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <x-ui.input type="text" name="name" id="edit-name" x-bind:value="editData.name" required />
                </div>
                <div>
                    <label for="edit-email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <x-ui.input type="email" name="email" id="edit-email" x-bind:value="editData.email" required />
                </div>
                <div>
                    <label for="edit-password" class="block text-sm font-medium text-slate-700 mb-1">Password Baru (kosongkan jika tidak diubah)</label>
                    <x-ui.input type="password" name="password" id="edit-password" minlength="6" />
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <x-ui.secondary-button type="button" @click="$dispatch('close-modal', 'edit-staff-modal')">Batal</x-ui.secondary-button>
                    <x-ui.primary-button type="submit">Perbarui</x-ui.primary-button>
                </div>
            </form>
        </x-ui.modal>

        {{-- Delete Modal --}}
        <x-ui.confirm-delete-modal name="delete-staff-modal" :action="'#'" x-bind:action="deleteUrl" title="Hapus Staff" message="Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan." />

    </div>
</x-layout.app>
