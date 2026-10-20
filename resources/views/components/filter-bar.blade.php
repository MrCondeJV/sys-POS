@props([
    'action' => null,
    'method' => 'GET',
    'resetUrl' => null,
    'clearUrl' => null,
    'hasActiveFilters' => false,
    'count' => null,
])

@php
    $resolvedResetUrl = $resetUrl ?? $clearUrl ?? url()->current();
@endphp

<div {{ $attributes->merge(['class' => 'card p-4 sm:p-5 bg-surface shadow-xs']) }}>
    <form method="{{ $method }}" action="{{ $action ?? url()->current() }}">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-end">
            {{ $slot }}

            @if(isset($actions))
                {{ $actions }}
            @else
                <div class="col-span-full lg:col-span-2 flex items-center gap-2 pt-1 sm:pt-0">
                    <x-button type="submit" variant="primary" size="lg" icon="filter" class="w-full justify-center">
                        Filtrar
                    </x-button>

                    @if($hasActiveFilters && $resolvedResetUrl)
                        <a href="{{ $resolvedResetUrl }}"
                           class="h-10 w-10 shrink-0 inline-flex items-center justify-center rounded-lg border border-border-strong text-text-muted hover:text-text-primary hover:bg-surface-muted hover:border-border transition"
                           title="Restablecer todos los filtros">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="sr-only">Limpiar filtros</span>
                        </a>
                    @endif

                    @if($count !== null)
                        <span class="text-xs text-text-secondary tabular-nums ml-auto">
                            {{ $count }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </form>
</div>
