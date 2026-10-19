@props([
    'type' => null,
    'message' => null,
    'duration' => 4000,
])

@php
    $flashType = $type ?? (
        session()->has('success') ? 'success' : (
        session()->has('error') ? 'error' : (
        session()->has('warning') ? 'warning' : (
        session()->has('info') ? 'info' : (
        session()->has('status') ? 'success' : null))))
    );

    $flashMessage = $message ?? (
        session('success') ?? (
        session('error') ?? (
        session('warning') ?? (
        session('info') ?? (
        session('status') ?? null))))
    );

    $styleMap = [
        'success' => [
            'border' => 'border-emerald-200',
            'bg' => 'bg-white',
            'iconBg' => 'bg-emerald-50 text-emerald-600',
            'title' => 'Éxito',
        ],
        'error' => [
            'border' => 'border-rose-200',
            'bg' => 'bg-white',
            'iconBg' => 'bg-rose-50 text-rose-600',
            'title' => 'Error',
        ],
        'warning' => [
            'border' => 'border-amber-200',
            'bg' => 'bg-white',
            'iconBg' => 'bg-amber-50 text-amber-600',
            'title' => 'Atención',
        ],
        'info' => [
            'border' => 'border-sky-200',
            'bg' => 'bg-white',
            'iconBg' => 'bg-sky-50 text-sky-600',
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
        class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border {{ $currentStyle['border'] }} p-4 pointer-events-auto"
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
                <p class="text-xs font-semibold text-slate-900">
                    {{ $currentStyle['title'] }}
                </p>
                <p class="text-xs text-slate-600 mt-0.5">
                    {{ $flashMessage }}
                </p>
            </div>

            <button
                type="button"
                x-on:click="show = false"
                class="text-slate-400 hover:text-slate-600 focus:outline-none p-1 rounded-md"
                aria-label="Cerrar notificación"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif
