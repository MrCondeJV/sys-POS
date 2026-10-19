@props([
    'variant' => 'neutral',
    'size' => 'sm',
    'dot' => false,
])

@php
    $variantClass = match ($variant) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'info' => 'badge-info',
        default => 'badge-neutral',
    };

    $sizeClass = match ($size) {
        'md' => 'badge-md',
        default => 'badge-sm',
    };

    $dotColor = match ($variant) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'info' => 'bg-sky-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$variantClass} {$sizeClass}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} shrink-0" aria-hidden="true"></span>
    @endif
    <span>{{ $slot }}</span>
</span>
