@props(['timeout' => 5000])

<div x-data="{ 
        notifications: [],
        add(message, type = 'success') {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            if (this.timeout) {
                setTimeout(() => this.remove(id), this.timeout);
            }
        },
        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        },
        timeout: {{ $timeout }}
    }"
    @notify.window="add($event.detail.message, $event.detail.type)"
    class="fixed bottom-4 right-4 z-50 flex flex-col gap-3 pointer-events-none"
>
    <!-- Server-side Session Messages -->
    @if (session()->has('success'))
        <div x-init="add('{{ session('success') }}', 'success')"></div>
    @endif
    @if (session()->has('error'))
        <div x-init="add('{{ session('error') }}', 'error')"></div>
    @endif
    @if (session()->has('warning'))
        <div x-init="add('{{ session('warning') }}', 'warning')"></div>
    @endif
    @if ($errors->any())
        <div x-init="add('Error: {{ $errors->first() }}', 'error')"></div>
    @endif

    <template x-for="notification in notifications" :key="notification.id">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="pointer-events-auto w-full max-w-md min-w-[24rem] overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 relative"
             :class="{
                'bg-white': true,
                'border-l-4 border-green-500': notification.type === 'success',
                'border-l-4 border-red-500': notification.type === 'error',
                'border-l-4 border-yellow-500': notification.type === 'warning',
             }"
        >
            <div class="p-4 flex items-start">
                <div class="flex-shrink-0">
                    <template x-if="notification.type === 'success'">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </template>
                    <template x-if="notification.type === 'error'">
                        <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </template>
                    <template x-if="notification.type === 'warning'">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </template>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900" x-text="notification.message"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="remove(notification.id)" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
