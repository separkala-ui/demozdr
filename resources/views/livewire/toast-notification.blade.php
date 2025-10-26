<div 
    class="pointer-events-none fixed left-4 top-4 z-[9999] flex flex-col gap-2 sm:left-auto sm:right-4"
    x-data="toastManager()"
    @toast-added.window="onToastAdded($event.detail)"
>
    @foreach($notifications as $notification)
        <div
            x-data="{ 
                show: false,
                id: '{{ $notification['id'] }}',
                type: '{{ $notification['type'] }}',
                removing: false
            }"
            x-init="
                setTimeout(() => show = true, 10);
                setTimeout(() => {
                    removing = true;
                    setTimeout(() => {
                        show = false;
                        setTimeout(() => $wire.removeNotification(id), 300);
                    }, 200);
                }, {{ $notification['duration'] }});
            "
            x-show="show"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            :class="removing ? 'animate-toast-fade-out' : ''"
            class="pointer-events-auto flex w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
            :class="{
                'bg-white': type === 'info',
                'bg-emerald-50': type === 'success',
                'bg-rose-50': type === 'error',
                'bg-amber-50': type === 'warning',
                'bg-indigo-50': type === 'info'
            }"
            role="alert"
            aria-live="assertive"
        >
            <!-- Icon -->
            <div class="flex w-12 shrink-0 items-center justify-center">
                <template x-if="type === 'success'">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                        <iconify-icon icon="lucide:check-circle" class="text-2xl text-emerald-600"></iconify-icon>
                    </div>
                </template>
                <template x-if="type === 'error'">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-100">
                        <iconify-icon icon="lucide:x-circle" class="text-2xl text-rose-600"></iconify-icon>
                    </div>
                </template>
                <template x-if="type === 'warning'">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                        <iconify-icon icon="lucide:alert-triangle" class="text-2xl text-amber-600"></iconify-icon>
                    </div>
                </template>
                <template x-if="type === 'info'">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100">
                        <iconify-icon icon="lucide:info" class="text-2xl text-indigo-600"></iconify-icon>
                    </div>
                </template>
            </div>

            <!-- Content -->
            <div class="flex flex-1 items-center py-4 pr-3">
                <p 
                    class="text-sm font-medium"
                    :class="{
                        'text-slate-900': type === 'info',
                        'text-emerald-900': type === 'success',
                        'text-rose-900': type === 'error',
                        'text-amber-900': type === 'warning',
                        'text-indigo-900': type === 'info'
                    }"
                >
                    {{ $notification['message'] }}
                </p>
            </div>

            <!-- Close Button -->
            <div class="flex items-center pl-2 pr-3">
                <button
                    @click="
                        removing = true;
                        setTimeout(() => {
                            show = false;
                            setTimeout(() => $wire.removeNotification(id), 300);
                        }, 200);
                    "
                    class="inline-flex rounded-md p-1.5 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                    :class="{
                        'text-slate-400 hover:bg-slate-100 focus:ring-slate-500': type === 'info',
                        'text-emerald-500 hover:bg-emerald-100 focus:ring-emerald-500': type === 'success',
                        'text-rose-500 hover:bg-rose-100 focus:ring-rose-500': type === 'error',
                        'text-amber-500 hover:bg-amber-100 focus:ring-amber-500': type === 'warning',
                        'text-indigo-500 hover:bg-indigo-100 focus:ring-indigo-500': type === 'info'
                    }"
                >
                    <span class="sr-only">{{ __('بستن') }}</span>
                    <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
                </button>
            </div>

            <!-- Progress Bar -->
            <div 
                class="absolute bottom-0 left-0 h-1 w-full origin-right"
                :class="{
                    'bg-emerald-200': type === 'success',
                    'bg-rose-200': type === 'error',
                    'bg-amber-200': type === 'warning',
                    'bg-indigo-200': type === 'info'
                }"
                x-show="!removing"
            >
                <div 
                    class="h-full"
                    :class="{
                        'bg-emerald-500': type === 'success',
                        'bg-rose-500': type === 'error',
                        'bg-amber-500': type === 'warning',
                        'bg-indigo-500': type === 'info'
                    }"
                    x-init="
                        $el.style.width = '100%';
                        setTimeout(() => {
                            $el.style.transition = 'width {{ $notification['duration'] }}ms linear';
                            $el.style.width = '0%';
                        }, 50);
                    "
                ></div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    function toastManager() {
        return {
            onToastAdded(detail) {
                // Additional handling if needed
                console.log('Toast added:', detail);
            }
        };
    }

    // Global toast helper
    window.toast = {
        success(message, duration = 5000) {
            window.Livewire.dispatch('showToast', { 
                message, 
                type: 'success', 
                duration 
            });
        },
        error(message, duration = 5000) {
            window.Livewire.dispatch('showToast', { 
                message, 
                type: 'error', 
                duration 
            });
        },
        warning(message, duration = 5000) {
            window.Livewire.dispatch('showToast', { 
                message, 
                type: 'warning', 
                duration 
            });
        },
        info(message, duration = 5000) {
            window.Livewire.dispatch('showToast', { 
                message, 
                type: 'info', 
                duration 
            });
        }
    };
</script>
@endpush

@push('styles')
<style>
    @keyframes toast-fade-out {
        0% {
            opacity: 1;
            transform: translateX(0);
        }
        100% {
            opacity: 0;
            transform: translateX(100%);
        }
    }

    .animate-toast-fade-out {
        animation: toast-fade-out 0.2s ease-in forwards;
    }
</style>
@endpush

