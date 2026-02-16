@props(['name', 'action' => '#', 'mode' => 'delete', 'title' => 'Konfirmasi Hapus', 'message' => 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.'])

<x-ui.modal :name="$name" :title="$title" maxWidth="md">
    <div class="flex gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center text-rose-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $title }}</h3>
            <p class="text-sm text-slate-500">{{ $message }}</p>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" class="mt-6 flex justify-end gap-3" x-data>
        @csrf
        @method('DELETE')
        
        <x-ui.secondary-button type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')">
            Batal
        </x-ui.secondary-button>
        
        <x-ui.primary-button type="submit" class="!bg-rose-600 hover:!bg-rose-700 focus:!ring-rose-500">
            Hapus Data
        </x-ui.primary-button>
    </form>
</x-ui.modal>
