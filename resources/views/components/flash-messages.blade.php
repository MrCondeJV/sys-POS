@if(session('success'))
    <div class="p-4 rounded-lg bg-success-bg border border-success-border text-success-text flex items-center space-x-2">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="p-4 rounded-lg bg-danger-bg border border-danger-border text-danger-text flex items-center space-x-2">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-semibold">{{ session('error') }}</span>
    </div>
@endif
