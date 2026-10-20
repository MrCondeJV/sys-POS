@props([
    'title' => 'No se encontraron registros',
    'description' => 'No hay elementos para mostrar en este momento.',
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state flex flex-col items-center justify-center text-center p-8 sm:p-12 border border-dashed border-border rounded-xl bg-surface-muted/60']) }}>
    <div class="w-10 h-10 flex items-center justify-center text-slate-400 mb-3 shrink-0">
        @if($icon || isset($customIcon))
            {{ $customIcon ?? '' }}
            @if($icon) {!! $icon !!} @endif
        @else
            <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        @endif
    </div>

    <h4 class="text-sm font-semibold text-text-primary tracking-tight">
        {{ $title }}
    </h4>

    @if($description)
        <p class="text-xs sm:text-[13px] text-text-secondary max-w-sm mt-1">
            {{ $description }}
        </p>
    @endif

    @if($slot->isNotEmpty() || isset($action))
        <div class="mt-4 flex items-center gap-2">
            {{ $action ?? $slot }}
        </div>
    @endif
</div>
