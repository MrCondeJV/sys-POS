@props([
    'variant' => 'primary', // primary, secondary, outline, danger, ghost
    'size' => 'md', // sm (32px), md (36px), lg (40px)
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'loading' => false,
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none rounded-md';

    $sizeClasses = match ($size) {
        'sm' => 'h-8 px-2.5 text-xs gap-1.5',
        'lg' => 'h-10 px-4 text-sm gap-2',
        default => 'h-9 px-3.5 text-xs sm:text-[13px] gap-2', // md = 36px
    };

    $variantClasses = match ($variant) {
        'secondary' => 'bg-white text-slate-700 border border-slate-300 shadow-xs hover:bg-slate-50 hover:text-slate-900 focus-visible:ring-slate-400',
        'outline' => 'bg-transparent text-slate-700 border border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus-visible:ring-brand-600',
        'danger' => 'bg-rose-600 text-white shadow-xs hover:bg-rose-700 focus-visible:ring-rose-600',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-slate-400',
        default => 'bg-brand-600 text-white shadow-xs hover:bg-brand-500 focus-visible:ring-brand-600', // primary
    };

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href)
        href="{{ $href }}"
    @else
        type="{{ $type }}"
        @if($disabled) disabled @endif
    @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($loading)
        <svg class="animate-spin -ml-0.5 mr-1.5 h-3.5 w-3.5 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon && $iconPosition === 'left')
        <span class="shrink-0 leading-none">{!! $icon !!}</span>
    @endif

    <span>{{ $slot }}</span>

    @if(!$loading && $icon && $iconPosition === 'right')
        <span class="shrink-0 leading-none">{!! $icon !!}</span>
    @endif
</{{ $tag }}>
