@props([
    'variant' => 'ghost', // ghost, secondary, outline, danger, primary
    'size' => 'sm', // sm (32px), md (36px)
    'type' => 'button',
    'href' => null,
    'title' => null,
    'ariaLabel' => null,
    'disabled' => false,
])

@php
    $accessibleLabel = $ariaLabel ?? $title ?? 'Acción';

    $baseClasses = 'inline-flex items-center justify-center rounded-md font-medium transition-all select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none shrink-0';

    $sizeClasses = match ($size) {
        'md' => 'w-9 h-9 text-sm',
        default => 'w-8 h-8 text-xs', // 32px
    };

    $variantClasses = match ($variant) {
        'secondary' => 'bg-white text-slate-700 border border-slate-300 shadow-xs hover:bg-slate-50 hover:text-slate-900 focus-visible:ring-slate-400',
        'outline' => 'bg-transparent text-slate-700 border border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus-visible:ring-brand-600',
        'danger' => 'bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-600 hover:text-white focus-visible:ring-rose-600',
        'primary' => 'bg-brand-600 text-white shadow-xs hover:bg-brand-500 focus-visible:ring-brand-600',
        default => 'bg-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-800 focus-visible:ring-slate-400', // ghost
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
    @if($title) title="{{ $title }}" @endif
    aria-label="{{ $accessibleLabel }}"
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</{{ $tag }}>
