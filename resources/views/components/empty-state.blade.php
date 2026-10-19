@props([
    'title' => 'No se encontraron registros',
    'description' => 'No hay elementos para mostrar en este momento.',
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center p-8 sm:p-12 border border-dashed border-slate-200 rounded-lg bg-slate-50/50']) }}>
    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3 shrink-0">
        @if($icon || isset($customIcon))
            {{ $customIcon ?? '' }}
            @if($icon) {!! $icon !!} @endif
        @else
            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        @endif
    </div>

    <h4 class="text-sm font-semibold text-slate-900 tracking-tight">
        {{ $title }}
    </h4>

    @if($description)
        <p class="text-xs sm:text-[13px] text-slate-500 max-w-sm mt-1">
            {{ $description }}
        </p>
    @endif

    @if($slot->isNotEmpty() || isset($action))
        <div class="mt-4 flex items-center gap-2">
            {{ $action ?? $slot }}
        </div>
    @endif
</div>
