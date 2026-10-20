@props([
    'type' => null,
    'message' => null,
    'duration' => 4000,
    'listenSession' => false, // Por defecto false porque layouts/partials/flash-messages.blade.php ya gestiona la sesión global
])

@php
    $flashType = $type ?? ($listenSession ? (
        session()->has('success') ? 'success' : (
        session()->has('error') ? 'error' : (
        session()->has('warning') ? 'warning' : (
        session()->has('info') ? 'info' : (
        session()->has('status') ? 'success' : null))))
    ) : null);

    $flashMessage = $message ?? ($listenSession ? (
        session('success') ?? (
        session('error') ?? (
        session('warning') ?? (
        session('info') ?? (
        session('status') ?? null))))
    ) : null);

    $styleMap = [
        'success' => [
            'border' => 'border-success-border',
            'iconBg' => 'bg-success-bg text-success-text',
            'title' => 'Operación exitosa',
        ],
        'error' => [
            'border' => 'border-danger-border',
            'iconBg' => 'bg-danger-bg text-danger-text',
            'title' => 'Error',
        ],
        'warning' => [
            'border' => 'border-warning-border',
            'iconBg' => 'bg-warning-bg text-warning-text',
            'title' => 'Atención',
        ],
        'info' => [
            'border' => 'border-info-border',
            'iconBg' => 'bg-info-bg text-info-text',
            'title' => 'Información',
        ],
    ];

    $currentStyle = $styleMap[$flashType] ?? $styleMap['info'];
@endphp

@if($flashMessage)
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, {{ $duration }})"
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-4 right-4 z-50 max-w-sm w-full bg-surface rounded-xl shadow-lg border {{ $currentStyle['border'] }} p-4 pointer-events-auto"
        role="alert"
    >
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $currentStyle['iconBg'] }}">
                @if($flashType === 'success')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                @elseif($flashType === 'error')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                @elseif($flashType === 'warning')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                @else
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>

            <div class="flex-1 pt-0.5">
                <p class="text-xs font-semibold text-text-primary">
                    {{ $currentStyle['title'] }}
                </p>
                <p class="text-xs text-text-secondary mt-0.5">
                    {{ $flashMessage }}
                </p>
            </div>

            <button
                type="button"
                x-on:click="show = false"
                class="text-text-muted hover:text-text-primary focus:outline-none p-1 rounded-md"
                aria-label="Cerrar notificación"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif
