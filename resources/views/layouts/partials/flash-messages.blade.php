<div class="w-full max-w-7xl mx-auto">
    @if(session('success'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition-all ease-in-out duration-500"
         x-transition:leave-start="opacity-100 max-h-40 transform translate-y-0"
         x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
         class="mb-6 bg-success-bg border border-success-border p-4 rounded-lg flex items-center justify-between shadow-xs transition-all"
         role="alert">
        <div class="flex items-center gap-3 text-success-text text-sm font-medium">
            <svg class="w-5 h-5 text-success flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button @click="show = false" type="button" class="text-success-text hover:opacity-75 p-1 rounded-lg transition cursor-pointer" title="Cerrar" aria-label="Cerrar alerta">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition-all ease-in-out duration-500"
         x-transition:leave-start="opacity-100 max-h-40 transform translate-y-0"
         x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
         class="mb-6 bg-danger-bg border border-danger-border p-4 rounded-lg flex items-center justify-between shadow-xs transition-all"
         role="alert">
        <div class="flex items-center gap-3 text-danger-text text-sm font-medium">
            <svg class="w-5 h-5 text-danger flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button @click="show = false" type="button" class="text-danger-text hover:opacity-75 p-1 rounded-lg transition cursor-pointer" title="Cerrar" aria-label="Cerrar alerta">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if(session('warning'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition-all ease-in-out duration-500"
         x-transition:leave-start="opacity-100 max-h-40 transform translate-y-0"
         x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
         class="mb-6 bg-warning-bg border border-warning-border p-4 rounded-lg flex items-center justify-between shadow-xs transition-all"
         role="alert">
        <div class="flex items-center gap-3 text-warning-text text-sm font-medium">
            <svg class="w-5 h-5 text-warning flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>{{ session('warning') }}</span>
        </div>
        <button @click="show = false" type="button" class="text-warning-text hover:opacity-75 p-1 rounded-lg transition cursor-pointer" title="Cerrar" aria-label="Cerrar alerta">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if(session('status') || session('info'))
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition-all ease-in-out duration-500"
         x-transition:leave-start="opacity-100 max-h-40 transform translate-y-0"
         x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
         class="mb-6 bg-info-bg border border-info-border p-4 rounded-lg flex items-center justify-between shadow-xs transition-all"
         role="alert">
        <div class="flex items-center gap-3 text-info-text text-sm font-medium">
            <svg class="w-5 h-5 text-info flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('status') ?? session('info') }}</span>
        </div>
        <button @click="show = false" type="button" class="text-info-text hover:opacity-75 p-1 rounded-lg transition cursor-pointer" title="Cerrar" aria-label="Cerrar alerta">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 7000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition-all ease-in-out duration-500"
         x-transition:leave-start="opacity-100 max-h-60 transform translate-y-0"
         x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
         class="mb-6 bg-danger-bg border border-danger-border p-4 rounded-lg shadow-xs flex items-start justify-between transition-all"
         role="alert">
        <div class="flex items-start gap-3 text-danger-text text-sm">
            <svg class="w-5 h-5 text-danger flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-1">
                @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
                @endforeach
            </div>
        </div>
        <button @click="show = false" type="button" class="text-danger-text hover:opacity-75 p-1 rounded-lg transition ml-4 cursor-pointer" title="Cerrar" aria-label="Cerrar alerta">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif
</div>
