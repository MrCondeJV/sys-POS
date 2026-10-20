@props([
    'variant' => null,
    'status' => null,
    'color' => null,
    'size' => 'sm',
    'dot' => false,
])

@php
    $resolvedVariant = $variant ?? $status ?? $color ?? 'neutral';

    $variantClass = match ($resolvedVariant) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger', 'error' => 'badge-danger',
        'info', 'brand', 'primary' => 'badge-info',
        default => 'badge-neutral',
    };

    $sizeClass = match ($size) {
        'md' => 'badge-md',
        default => 'badge-sm',
    };

    $dotColor = match ($resolvedVariant) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger', 'error' => 'bg-danger',
        'info', 'brand', 'primary' => 'bg-brand-600',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$variantClass} {$sizeClass}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} shrink-0" aria-hidden="true"></span>
    @endif
    <span>{{ $slot }}</span>
</span>
