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
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-center">
            {{ $slot }}

            @if(isset($actions))
                {{ $actions }}
            @else
                <div class="sm:col-span-12 lg:col-auto flex items-center flex-wrap gap-2.5 pt-1 sm:pt-0">
                    <x-button type="submit" variant="secondary" size="md" icon="<svg class='w-3.5 h-3.5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z'/></svg>">
                        Filtrar
                    </x-button>

                    @if($hasActiveFilters && $resolvedResetUrl)
                        <a href="{{ $resolvedResetUrl }}"
                           class="inline-flex items-center gap-1 text-xs font-medium text-text-secondary hover:text-text-primary hover:underline p-1.5 transition"
                           title="Restablecer todos los filtros">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Limpiar filtros</span>
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
