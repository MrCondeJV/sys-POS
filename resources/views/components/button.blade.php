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
    $baseClasses = 'inline-flex items-center justify-center font-semibold transition-all select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none rounded-lg';

    $sizeClasses = match ($size) {
        'sm' => 'h-8 px-2.5 text-xs gap-1.5',
        'lg' => 'h-10 px-4 text-sm gap-2',
        default => 'h-9 px-3.5 text-xs sm:text-[13px] gap-2', // md = 36px
    };

    $variantClasses = match ($variant) {
        'secondary' => 'bg-surface text-text-primary border border-border-strong shadow-xs hover:bg-surface-muted focus-visible:ring-brand-600',
        'outline' => 'bg-transparent text-text-secondary border border-border-strong hover:bg-surface-muted hover:text-text-primary focus-visible:ring-brand-600',
        'danger' => 'bg-danger text-white shadow-xs hover:bg-danger-text focus-visible:ring-danger border border-transparent',
        'ghost' => 'bg-transparent text-text-secondary hover:bg-surface-muted hover:text-text-primary focus-visible:ring-brand-600 border border-transparent',
        default => 'bg-brand-600 text-white shadow-xs hover:bg-brand-500 focus-visible:ring-brand-600 border border-transparent', // primary
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
        <svg class="animate-spin mr-1.5 h-3.5 w-3.5 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
